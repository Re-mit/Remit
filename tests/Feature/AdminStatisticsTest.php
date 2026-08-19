<?php

namespace Tests\Feature;

use App\Models\Reservation;
use App\Models\Room;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class AdminStatisticsTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Carbon::setTestNow(Carbon::parse('2026-02-10 12:00:00', 'Asia/Seoul'));
        config()->set('app.timezone', 'Asia/Seoul');

        $this->createStatisticsSchema();
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    public function test_admin_can_view_aggregated_member_and_reservation_statistics(): void
    {
        $admin = $this->createUser('admin@example.com', 'admin');
        $firstUser = $this->createUser('first@example.com');
        $secondUser = $this->createUser('second@example.com');
        $cancelledUser = $this->createUser('cancelled@example.com');

        $room = Room::create(['name' => '가천관 622호']);

        $firstReservation = $this->createReservation($room, '2026-02-06 10:00:00');
        $firstReservation->users()->attach($firstUser->id, ['is_representative' => true]);
        $firstReservation->users()->attach($secondUser->id, ['is_representative' => false]);

        $secondReservation = $this->createReservation($room, '2026-02-08 14:00:00');
        $secondReservation->users()->attach($firstUser->id, ['is_representative' => true]);

        $cancelledReservation = $this->createReservation(
            $room,
            '2026-02-09 14:00:00',
            'cancelled',
            'user'
        );
        $cancelledReservation->users()->attach($cancelledUser->id, ['is_representative' => true]);

        $response = $this->actingAs($admin)->get(route('admin.statistics', ['period' => 7]));

        $response->assertOk();
        $response->assertViewIs('admin.statistics');
        $response->assertViewHas('period', 7);
        $response->assertViewHas('stats', function ($stats) {
            return $stats['totalMembers'] === 3
                && $stats['newMembers'] === 3
                && $stats['activeReservationUsers'] === 2
                && $stats['repeatUsers'] === 1
                && $stats['totalReservations'] === 3
                && $stats['confirmedReservations'] === 2
                && $stats['cancelledReservations'] === 1
                && $stats['cancellationRate'] === 33.3
                && $stats['averageParticipants'] === 1.5
                && $stats['userCancelled'] === 1
                && $stats['adminCancelled'] === 0;
        });
        $response->assertSee('회원·예약 현황');
        $response->assertSee('가천관 622호');
    }

    public function test_non_admin_cannot_view_statistics(): void
    {
        $user = $this->createUser('member@example.com');

        $this->actingAs($user)
            ->get(route('admin.statistics'))
            ->assertForbidden();
    }

    private function createUser(string $email, string $role = 'user'): User
    {
        return User::create([
            'name' => $role === 'admin' ? '관리자' : '사용자',
            'email' => $email,
            'password' => Hash::make('password'),
            'role' => $role,
            'warning' => 0,
        ]);
    }

    private function createReservation(
        Room $room,
        string $startAt,
        string $status = 'confirmed',
        ?string $cancelledBy = null
    ): Reservation {
        $start = Carbon::parse($startAt, 'Asia/Seoul');

        return Reservation::create([
            'room_id' => $room->id,
            'start_at' => $start,
            'end_at' => $start->copy()->addHour(),
            'key_code' => '1234',
            'status' => $status,
            'cancelled_at' => $status === 'cancelled' ? Carbon::now('Asia/Seoul') : null,
            'cancelled_by' => $cancelledBy,
        ]);
    }

    private function createStatisticsSchema(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('password');
            $table->string('role')->default('user');
            $table->integer('warning')->default(0);
            $table->timestamp('suspended_at')->nullable();
            $table->rememberToken();
            $table->timestamps();
        });

        Schema::create('rooms', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->timestamps();
        });

        Schema::create('reservations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('room_id');
            $table->dateTime('start_at');
            $table->dateTime('end_at');
            $table->string('key_code', 10);
            $table->string('status')->default('confirmed');
            $table->dateTime('cancelled_at')->nullable();
            $table->string('cancelled_by', 20)->nullable();
            $table->text('cancel_reason')->nullable();
            $table->timestamps();
        });

        Schema::create('reservation_users', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('reservation_id');
            $table->unsignedBigInteger('user_id');
            $table->boolean('is_representative')->default(false);
            $table->timestamps();
            $table->unique(['reservation_id', 'user_id']);
        });

        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->timestamp('read_at')->nullable();
            $table->timestamps();
        });
    }
}

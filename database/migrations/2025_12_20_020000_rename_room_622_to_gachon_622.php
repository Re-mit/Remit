<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // 기존 데이터가 '622호'로 저장되어 있으면 '가천관 622호'로 변경
        // (name 컬럼이 unique이므로 충돌 시에는 안전하게 기존 레코드를 업데이트/병합)
        $old = DB::table('rooms')->where('name', '622호')->first();
        $new = DB::table('rooms')->where('name', '가천관 622호')->first();

        if ($old && !$new) {
            DB::table('rooms')->where('id', $old->id)->update(['name' => '가천관 622호']);
        }

        if ($old && $new) {
            // 둘 다 존재하면: 예약/연결은 room_id로 되어 있으니 old 레코드를 삭제하지 않고,
            // old name만 변경하려 하면 unique 충돌. 그래서 old 레코드는 유지하되 name만 다른 값으로 바꾸지 않고 종료.
            // 운영 시 하나로 정리하고 싶다면 room_id 리매핑 마이그레이션이 필요함.
        }
    }

    public function down(): void
    {
        $new = DB::table('rooms')->where('name', '가천관 622호')->first();
        $old = DB::table('rooms')->where('name', '622호')->first();

        if ($new && !$old) {
            DB::table('rooms')->where('id', $new->id)->update(['name' => '622호']);
        }
    }
};




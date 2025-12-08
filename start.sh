#!/bin/bash

# Remit Laravel Server Startup Script
# Mobile-accessible server on local network

echo "🚀 Starting Remit Server Setup..."
echo ""

# Get local IP address
LOCAL_IP=$(ipconfig getifaddr en0)

if [ -z "$LOCAL_IP" ]; then
    echo "⚠️  Could not detect local IP address"
    echo "    Using localhost instead"
    LOCAL_IP="localhost"
fi

# Check and install npm dependencies
if [ ! -d "node_modules" ]; then
    echo "📦 Installing npm dependencies..."
    npm install
    echo ""
fi

# Build frontend assets
echo "🔨 Building frontend assets..."
npm run build
echo ""

echo "✅ Setup complete!"
echo ""
echo "📍 Server will be accessible at:"
echo "   - Local:   http://localhost:8000"
echo "   - Network: http://$LOCAL_IP:8000"
echo ""
echo "📱 To access from mobile device:"
echo "   1. Connect your phone to the same Wi-Fi network"
echo "   2. Open browser and go to: http://$LOCAL_IP:8000"
echo ""
echo "⚠️  Note: Update .env APP_URL to http://$LOCAL_IP:8000"
echo "    And add this URL to Google OAuth redirect URIs"
echo ""
echo "Press Ctrl+C to stop the server"
echo "════════════════════════════════════════════════════"
echo ""

# Start Laravel development server
php artisan serve --host=0.0.0.0 --port=8000

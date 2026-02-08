#!/bin/bash

PORT=5173

PID=$(lsof -t -i :$PORT)

if [ -z "$PID" ]; then
    echo "✅ Tiada proses Vite pada port $PORT"
else
    echo "⚠️ Kill Vite pada port $PORT (PID: $PID)"
    kill -9 $PID
    echo "🔥 Port $PORT berjaya ditutup"
fi

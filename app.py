import asyncio
import os
import threading
import sys

# Forzar salida inmediata en los logs de Render
sys.stdout.flush()

# Configurar event loop para Python 3.14
try:
    asyncio.get_event_loop()
except RuntimeError:
    loop = asyncio.new_event_loop()
    asyncio.set_event_loop(loop)

from flask import Flask

web_app = Flask(__name__)

@web_app.route("/")
def home():
    return "Servidor de Telegram y Python activo 24/7!"

def run_flask():
    port = int(os.environ.get("PORT", 10000))
    web_app.run(host="0.0.0.0", port=port, use_reloader=False)

if __name__ == "__main__":
    print(">>> [1/2] Iniciando servidor web de respaldo...", flush=True)
    flask_thread = threading.Thread(target=run_flask, daemon=True)
    flask_thread.start()

    print(">>> [2/2] Intentando arrancar el bot de Telegram...", flush=True)
    try:
        import telegram.sync
        telegram.sync.app.run()
    except Exception as e:
        print(f"❌ ERROR FATAL AL ARRANCAR TELEGRAM: {e}", flush=True)
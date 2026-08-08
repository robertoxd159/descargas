import os
import threading
from flask import Flask
# Esto importa tu script, lo que ejecuta el código global y prepara el 'app' de Pyrogram
import telegram.sync 

web_app = Flask(__name__)

@web_app.route("/")
def home():
    return "Servidor de Telegram y Python activo 24/7!"

def run_flask():
    port = int(os.environ.get("PORT", 10000))
    web_app.run(host="0.0.0.0", port=port, use_reloader=False)

if __name__ == "__main__":
    # 1. Arrancamos Flask en segundo plano
    print(">>> Iniciando servidor web de respaldo...")
    flask_thread = threading.Thread(target=run_flask, daemon=True)
    flask_thread.start()

    # 2. Arrancamos Telegram en el hilo principal
    print(">>> Iniciando el bot de Telegram en el hilo principal...")
    telegram.sync.app.run()

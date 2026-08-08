import asyncio
import os
import threading

# 1. Creamos y asignamos un event loop para que Pyrogram no falle al importarse en Python 3.14
try:
  asyncio.get_event_loop()
except RuntimeError:
  loop = asyncio.new_event_loop()
  asyncio.set_event_loop(loop)

from flask import Flask
# 2. Ahora sí importamos tu script de Telegram con seguridad
import telegram.sync

web_app = Flask(__name__)


@web_app.route("/")
def home():
  return "Servidor de Telegram y Python activo 24/7!"


def run_flask():
  port = int(os.environ.get("PORT", 10000))
  web_app.run(host="0.0.0.0", port=port, use_reloader=False)


if __name__ == "__main__":
  print(">>> Iniciando servidor web de respaldo...")
  flask_thread = threading.Thread(target=run_flask, daemon=True)
  flask_thread.start()

  print(">>> Iniciando el bot de Telegram en el hilo principal...")
  telegram.sync.app.run()

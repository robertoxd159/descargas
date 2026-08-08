import os
import threading
from flask import Flask

# Importamos el cliente de telegram desde la carpeta telegram/sync.py
from telegram.sync import app as telegram_app

web_app = Flask(__name__)


@web_app.route("/")
def home():
  return "Servidor de Telegram y Python activo 24/7!"


def run_telegram_bot():
  """Arranca el cliente de Pyrogram en segundo plano"""
  try:
    print("Iniciando bot de Telegram en segundo plano...")
    telegram_app.run()
  except Exception as e:
    print(f"Error en el hilo de Telegram: {e}")


if __name__ == "__main__":
  # 1. Arranca Telegram en paralelo
  bot_thread = threading.Thread(target=run_telegram_bot, daemon=True)
  bot_thread.start()

  # 2. Arranca Flask para mantener Render despierto
  port = int(os.environ.get("PORT", 5000))
  web_app.run(host="0.0.0.0", port=port)

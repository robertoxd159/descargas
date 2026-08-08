import os
import threading
from flask import Flask
# Importamos directamente tu script de sincronización
import telegram.sync

app = Flask(__name__)


@app.route("/")
def home():
  return "Servidor de Telegram y Python activo 24/7!"


def run_flask():
  """Corre el servidor web en un hilo separado para que no interfiera con Telegram"""
  port = int(os.environ.get("PORT", 10000))
  # Usamos use_reloader=False para evitar que Flask duplique los hilos
  app.run(host="0.0.0.0", port=port, use_reloader=False)


if __name__ == "__main__":
  print(">>> Iniciando servidor web de respaldo en segundo plano...")
  flask_thread = threading.Thread(target=run_flask, daemon=True)
  flask_thread.start()

  print(">>> Iniciando el bot de Telegram en el hilo principal...")
  # Pyrogram corre directamente en el hilo principal donde sí hay un event loop nativo
  telegram.sync.main()  # O si tu sync.py arranca solo al importarse o con app.run(), ajústalo abajo

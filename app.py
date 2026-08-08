import os
import subprocess
from flask import Flask

app = Flask(__name__)


@app.route("/")
def home():
  return "Servidor de Telegram y Python activo 24/7!"


if __name__ == "__main__":
  # 1. Arrancamos tu script de Telegram exactamente como lo corres en local
  print(">>> Iniciando el bot de Telegram en segundo plano...")
  subprocess.Popen(["python", "telegram/sync.py"])

  # 2. Arrancamos Flask para que Render mantenga la app viva
  port = int(os.environ.get("PORT", 10000))
  app.run(host="0.0.0.0", port=port)

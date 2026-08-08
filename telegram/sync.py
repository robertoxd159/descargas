import asyncio
import os
import threading
import sys
import mysql.connector
from flask import Flask, jsonify

# Forzar salida inmediata en los logs de Render
sys.stdout.flush()

try:
    asyncio.get_event_loop()
except RuntimeError:
    loop = asyncio.new_event_loop()
    asyncio.set_event_loop(loop)

web_app = Flask(__name__)

# Función para conectar a TiDB desde la API
def get_db_connection():
    return mysql.connector.connect(
        host=os.environ.get("DB_HOST", "gateway01.eu-central-1.prod.aws.tidbcloud.com"), 
        port=4000,
        user=os.environ.get("DB_USER", "396CdPhsCTxPorF.root"),              
        password=os.environ.get("DB_PASS", "eTQLFL4CRZkoeu1l"),         
        database=os.environ.get("DB_NAME", "test"),                          
        ssl_disabled=False,
        connect_timeout=10              
    )

@web_app.route("/")
def home():
    return "Servidor de Telegram y Python activo 24/7!"

# ¡NUEVO!: Este es el endpoint que consumirá InfinityFree
@web_app.route("/api/proyectos")
def api_proyectos():
    try:
        db = get_db_connection()
        # dictionary=True hace que los resultados salgan listos para JSON
        cursor = db.cursor(dictionary=True)
        cursor.execute("SELECT * FROM projects ORDER BY fecha_publicacion DESC")
        proyectos = cursor.fetchall()
        cursor.close()
        db.close()
        return jsonify(proyectos)
    except Exception as e:
        return jsonify({"error": str(e)}), 500

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
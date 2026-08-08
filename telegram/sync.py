import os
import mysql.connector
from pyrogram import Client, filters

BASE_DIR = os.path.dirname(os.path.abspath(__file__))
SESSION_NAME = os.path.join(BASE_DIR, "mi_sesion")

# Leer variables de entorno en Render
API_ID = os.environ.get("38390744")
API_HASH = os.environ.get("0679d4905087b36cf2f3feaba830c224")

# ¡ESTA ES LA LÍNEA QUE RENDER NO ENCONTRABA!
app = Client(SESSION_NAME, api_id=API_ID, api_hash=API_HASH)

def get_db_connection():
    # ... tu código de TiDB que pusimos antes ...
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
        # Importamos directamente la variable 'app' desde el archivo
        from telegram.sync import app as bot_app
        bot_app.run()
    except Exception as e:
        print(f"❌ ERROR FATAL AL ARRANCAR TELEGRAM: {e}", flush=True)
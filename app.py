import asyncio
import os
import threading
import sys
import subprocess
import mysql.connector
from flask import Flask, jsonify, request, send_file

sys.stdout.flush()

try:
    asyncio.get_event_loop()
except RuntimeError:
    loop = asyncio.new_event_loop()
    asyncio.set_event_loop(loop)

web_app = Flask(__name__)

@web_app.after_request
def after_request(response):
    response.headers.add('Access-Control-Allow-Origin', '*')
    response.headers.add('Access-Control-Allow-Headers', 'Content-Type,Authorization')
    response.headers.add('Access-Control-Allow-Methods', 'GET,PUT,POST,DELETE,OPTIONS')
    return response

# Conexión a TiDB
# Conexión fija a TiDB para evitar errores de variables de entorno
def get_db_connection():
    return mysql.connector.connect(
        host="gateway01.eu-central-1.prod.aws.tidbcloud.com", 
        port=4000,
        user="396CdPhsCTxPorF.root",              
        password="eTQLFL4CRZkoeu1l",         
        database="test",                          
        ssl_disabled=False,
        connect_timeout=10              
    )

@web_app.route("/")
def home():
    return "Servidor de Telegram y Python activo 24/7!"

@web_app.route("/api/proyectos")
def api_proyectos():
    try:
        db = get_db_connection()
        cursor = db.cursor(dictionary=True)
        cursor.execute("SELECT * FROM projects ORDER BY fecha_publicacion DESC")
        proyectos = cursor.fetchall()
        cursor.close()
        db.close()
        return jsonify(proyectos)
    except Exception as e:
        return jsonify({"error": str(e)}), 500

# NUEVO: Puente para descargas de Telegram
@web_app.route("/api/bajar_archivo")
def api_bajar_archivo():
    file_id = request.args.get("file_id")
    nombre = request.args.get("nombre", "descarga")
    if not file_id:
        return "Falta el ID del archivo", 400
        
    temp_file = f"/tmp/{nombre}.zip"
    script_path = os.path.join(os.path.dirname(__file__), "telegram", "download.py")
    
    # Ejecutamos tu script de Python tal como lo hacías en XAMPP, pero desde Render
    subprocess.run(["python", script_path, file_id, temp_file])
    
    if os.path.exists(temp_file):
        return send_file(temp_file, as_attachment=True, download_name=f"{nombre}.zip")
    else:
        return "Error al descargar el archivo desde Telegram", 500

# NUEVO: Puente para el Login y Registro
@web_app.route("/api/usuarios", methods=["POST"])
def api_usuarios():
    data = request.json
    accion = data.get("accion")
    email = data.get("email")
    password = data.get("password")
    
    try:
        db = get_db_connection()
        cursor = db.cursor(dictionary=True)
        
        if accion == "login":
            cursor.execute("SELECT * FROM users WHERE email = %s", (email,))
            user = cursor.fetchone()
            # Verificamos la contraseña
            if user and user['password'] == password: 
                return jsonify({"success": True, "user": user})
            return jsonify({"success": False, "message": "Credenciales incorrectas"})
            
        elif accion == "register":
            nombre = data.get("nombre")
            # Verificar si existe el correo
            cursor.execute("SELECT id FROM users WHERE email = %s", (email,))
            if cursor.fetchone():
                return jsonify({"success": False, "message": "El correo ya está registrado"})
                
            # Registrar nuevo usuario
            cursor.execute("INSERT INTO users (nombre, email, password, is_premium) VALUES (%s, %s, %s, 0)", 
                           (nombre, email, password))
            db.commit()
            return jsonify({"success": True})
            
    except Exception as e:
        return jsonify({"success": False, "error": str(e)})
    finally:
        if 'cursor' in locals(): cursor.close()
        if 'db' in locals(): db.close()

def run_flask():
    port = int(os.environ.get("PORT", 10000))
    web_app.run(host="0.0.0.0", port=port, use_reloader=False)

if __name__ == "__main__":
    print(">>> [1/2] Iniciando servidor web de respaldo...", flush=True)
    flask_thread = threading.Thread(target=run_flask, daemon=True)
    flask_thread.start()

    print(">>> [2/2] Intentando arrancar el bot de Telegram...", flush=True)
    try:
        from telegram.sync import app as bot_app
        bot_app.run()
    except Exception as e:
        print(f"❌ ERROR FATAL AL ARRANCAR TELEGRAM: {e}", flush=True)
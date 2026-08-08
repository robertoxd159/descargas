import os
import threading
import sys
import subprocess
import mysql.connector
from flask import Flask, jsonify, request, send_file
from telegram import Update
from telegram.ext import Application, MessageHandler, filters, ContextTypes

sys.stdout.flush()

# 1. Configuración de Flask
web_app = Flask(__name__)

@web_app.after_request
def after_request(response):
    response.headers.add('Access-Control-Allow-Origin', '*')
    response.headers.add('Access-Control-Allow-Headers', 'Content-Type,Authorization')
    response.headers.add('Access-Control-Allow-Methods', 'GET,PUT,POST,DELETE,OPTIONS')
    return response

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
            if user and user['password'] == password: 
                return jsonify({"success": True, "user": user})
            return jsonify({"success": False, "message": "Credenciales incorrectas"})
            
        elif accion == "register":
            nombre = data.get("nombre")
            cursor.execute("SELECT id FROM users WHERE email = %s", (email,))
            if cursor.fetchone():
                return jsonify({"success": False, "message": "El correo ya está registrado"})
                
            cursor.execute("INSERT INTO users (nombre, email, password, is_premium) VALUES (%s, %s, %s, 0)", 
                           (nombre, email, password))
            db.commit()
            return jsonify({"success": True})
            
    except Exception as e:
        return jsonify({"success": False, "error": str(e)})
    finally:
        if 'cursor' in locals(): cursor.close()
        if 'db' in locals(): db.close()


# 2. Lógica del Bot de Telegram Integrada
BOT_TOKEN = "8905133806:AAGiteJIjcInIMVgl186C3ouLKbLs3-i4eU"

def guardar_en_tidb(titulo, descripcion, categoria, imagen_url, telegram_file_id):
    try:
        db = get_db_connection()
        cursor = db.cursor()
        query = "INSERT INTO projects (titulo, descripcion, categoria, imagen_url, telegram_file_id) VALUES (%s, %s, %s, %s, %s)"
        cursor.execute(query, (titulo, descripcion, categoria, imagen_url, telegram_file_id))
        db.commit()
        cursor.close()
        db.close()
        print(">>> [EXITO] ¡Proyecto guardado automáticamente en TiDB desde Telegram!")
    except Exception as e:
        print(f"❌ Error al guardar en TiDB desde el bot: {e}")

async def manejar_archivo(update: Update, context: ContextTypes.DEFAULT_TYPE):
    mensaje = update.message
    documento = mensaje.document or mensaje.video or mensaje.audio or mensaje.photo
    
    if documento:
        if mensaje.photo:
            file_id = mensaje.photo[-1].file_id
            nombre_archivo = "Proyecto_Imagen"
        else:
            file_id = documento.file_id
            nombre_archivo = getattr(documento, 'file_name', 'Archivo_Sin_Nombre')

        caption = mensaje.caption or "Sin descripción"
        
        categoria = "General"
        titulo = nombre_archivo
        
        if "[" in caption and "]" in caption:
            try:
                parts = caption.split("]")
                categoria = parts[0].replace("[", "").strip()
                titulo = parts[1].strip() or nombre_archivo
            except:
                pass

        imagen_por_defecto = "https://images.unsplash.com/photo-1555066931-4365d14bab8c"
        guardar_en_tidb(titulo, caption, categoria, imagen_por_defecto, file_id)
        await mensaje.reply_text("✅ ¡Archivo recibido y sincronizado con tu web automáticamente!")


# 3. Arranque de los Servicios (Flask en hilo secundario, Telegram en principal)
def iniciar_web():
    port = int(os.environ.get("PORT", 10000))
    web_app.run(host="0.0.0.0", port=port, use_reloader=False)

if __name__ == "__main__":
    print(">>> [INICIO] Arrancando servidor web Flask en segundo plano...", flush=True)
    web_thread = threading.Thread(target=iniciar_web, daemon=True)
    web_thread.start()

    print(">>> [INICIO] Arrancando bot de Telegram en el hilo principal...", flush=True)
    try:
        bot_app = Application.builder().token(BOT_TOKEN).build()
        bot_app.add_handler(MessageHandler(filters.Document.ALL | filters.VIDEO | filters.AUDIO | filters.PHOTO, manejar_archivo))
        bot_app.run_polling()
    except Exception as e:
        print(f"❌ ERROR FATAL AL ARRANCAR TELEGRAM: {e}", flush=True)
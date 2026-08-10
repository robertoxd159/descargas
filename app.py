import os
import threading
import sys
import urllib.request
import json
import mysql.connector
from flask import Flask, jsonify, request, Response, stream_with_context
from telegram import Update
from telegram.ext import Application, MessageHandler, filters, ContextTypes, CommandHandler

sys.stdout.flush()

# Token del bot
BOT_TOKEN = "8905133806:AAGiteJIjcInIMVgl186C3ouLKbLs3-i4eU"

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

# RUTA DE DATOS PARA EL PANEL DE ADMINISTRACIÓN
@web_app.route("/api/admin_data")
def api_admin_data():
    try:
        db = get_db_connection()
        cursor = db.cursor(dictionary=True)
        
        # Estadísticas
        cursor.execute("SELECT SUM(monto) as total, COUNT(*) as ventas FROM payments WHERE estado='aprobado'")
        res_stats = cursor.fetchone()
        statsPagos = {"total": res_stats['total'] or 0, "ventas": res_stats['ventas'] or 0}
        
        cursor.execute("SELECT COUNT(*) as cuenta FROM users WHERE is_premium=1")
        usuariosActivos = cursor.fetchone()['cuenta']
        
        # Listados
        cursor.execute("SELECT * FROM users ORDER BY id DESC")
        usuarios = cursor.fetchall()
        
        cursor.execute("SELECT * FROM payments WHERE estado='pendiente' ORDER BY id DESC")
        pagos_pendientes = cursor.fetchall()
        
        # Configuración
        cursor.execute("SELECT * FROM settings")
        config = {row["key"]: row["value"] for row in cursor.fetchall()}
        
        cursor.close()
        db.close()
        
        return jsonify({
            "statsPagos": statsPagos,
            "usuariosActivos": usuariosActivos,
            "usuarios": usuarios,
            "pagos_pendientes": pagos_pendientes,
            "config": config
        })
    except Exception as e:
        return jsonify({"error": str(e)}), 500

@web_app.route("/api/admin/update_settings", methods=["POST"])
def api_admin_update_settings():
    try:
        # force=True es vital para que Flask no bloquee a InfinityFree
        data = request.get_json(force=True) 
        if not data:
            return jsonify({"success": False, "error": "No llegaron los datos desde la web"}), 400
            
        db = get_db_connection()
        cursor = db.cursor()
        for key, val in data.items():
            cursor.execute(
                "INSERT INTO settings (`key`, `value`) VALUES (%s, %s) ON DUPLICATE KEY UPDATE `value` = %s",
                (key, val, val)
            )
        db.commit()
        cursor.close()
        db.close()
        return jsonify({"success": True})
    except Exception as e:
        return jsonify({"success": False, "error": str(e)}), 500

@web_app.route("/api/admin/update_user", methods=["POST"])
def api_admin_update_user():
    data = request.json
    try:
        db = get_db_connection()
        cursor = db.cursor()
        cursor.execute("UPDATE users SET is_premium=1, premium_hasta=DATE_ADD(NOW(), INTERVAL %s DAY) WHERE id=%s", (data['dias'], data['user_id']))
        db.commit()
        cursor.close()
        db.close()
        return jsonify({"success": True})
    except Exception as e:
        return jsonify({"success": False, "error": str(e)}), 500

@web_app.route("/api/admin/update_payment", methods=["POST"])
def api_admin_update_payment():
    data = request.json
    try:
        db = get_db_connection()
        cursor = db.cursor(dictionary=True)
        cursor.execute("SELECT * FROM payments WHERE id=%s", (data['pago_id'],))
        pago = cursor.fetchone()
        if pago and pago['estado'] == 'pendiente':
            if data['accion_pago'] == 'aprobar':
                cursor.execute("UPDATE payments SET estado='aprobado' WHERE id=%s", (data['pago_id'],))
                dias = 180 if pago['plan'] == 'semestral' else (365 if pago['plan'] == 'anual' else 30)
                cursor.execute("UPDATE users SET is_premium=1, premium_hasta=DATE_ADD(NOW(), INTERVAL %s DAY) WHERE id=%s", (dias, pago['user_id']))
            else:
                cursor.execute("UPDATE payments SET estado='rechazado' WHERE id=%s", (data['pago_id'],))
            db.commit()
        cursor.close()
        db.close()
        return jsonify({"success": True})
    except Exception as e:
        return jsonify({"success": False, "error": str(e)}), 500

# RUTA DE DESCARGA DIRECTA DESDE TELEGRAM
@web_app.route("/api/bajar_archivo")
def api_bajar_archivo():
    file_id = request.args.get("file_id")
    nombre = request.args.get("nombre", "descarga")
    if not file_id:
        return "Falta el ID del archivo", 400
        
    try:
        get_file_url = f"https://api.telegram.org/bot{BOT_TOKEN}/getFile?file_id={file_id}"
        req = urllib.request.Request(get_file_url)
        with urllib.request.urlopen(req) as response:
            data = json.loads(response.read().decode())
            
        if not data.get("ok"):
            return "No se pudo obtener el archivo de Telegram", 404
            
        file_path = data["result"]["file_path"]
        download_url = f"https://api.telegram.org/file/bot{BOT_TOKEN}/{file_path}"
        
        ext = file_path.split('.')[-1] if '.' in file_path else 'zip'
        filename_final = f"{nombre}.{ext}"
        
        def generate():
            with urllib.request.urlopen(download_url) as file_stream:
                while True:
                    chunk = file_stream.read(8192)
                    if not chunk:
                        break
                    yield chunk
                    
        return Response(
            stream_with_context(generate()),
            content_type='application/octet-stream',
            headers={"Content-Disposition": f"attachment; filename=\"{filename_final}\""}
        )
    except Exception as e:
        return f"Error al procesar la descarga: {str(e)}", 500

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

        caption = mensaje.caption or ""
        
        titulo = nombre_archivo
        categoria = "General"
        descripcion = caption

        if caption:
            lineas = caption.split('\n')
            temp_titulo = ""
            temp_categoria = ""
            temp_desc = ""

            for linea in lineas:
                linea_limpia = linea.strip()
                if linea_limpia.lower().startswith("título:") or linea_limpia.lower().startswith("titulo:"):
                    temp_titulo = linea_limpia.split(":", 1)[1].strip()
                elif linea_limpia.lower().startswith("categoría:") or linea_limpia.lower().startswith("categoria:"):
                    temp_categoria = linea_limpia.split(":", 1)[1].strip()
                elif linea_limpia.lower().startswith("descripción:") or linea_limpia.lower().startswith("descripcion:"):
                    temp_desc = linea_limpia.split(":", 1)[1].strip()

            if temp_titulo: titulo = temp_titulo
            if temp_categoria: categoria = temp_categoria
            if temp_desc: descripcion = temp_desc

        imagen_por_defecto = "https://images.unsplash.com/photo-1555066931-4365d14bab8c"
        guardar_en_tidb(titulo, descripcion, categoria, imagen_por_defecto, file_id)
        await mensaje.reply_text(f"✅ ¡Proyecto sincronizado!\n📌 Título: {titulo}\n📂 Categoría: {categoria}")

async def eliminar_proyecto(update: Update, context: ContextTypes.DEFAULT_TYPE):
    mensaje = update.message
    if mensaje.reply_to_message:
        ref_msg = mensaje.reply_to_message
        documento = ref_msg.document or ref_msg.video or ref_msg.audio or (ref_msg.photo[-1] if ref_msg.photo else None)
        
        if documento:
            file_id = documento.file_id
            try:
                db = get_db_connection()
                cursor = db.cursor()
                cursor.execute("DELETE FROM projects WHERE telegram_file_id = %s", (file_id,))
                db.commit()
                afectados = cursor.rowcount
                cursor.close()
                db.close()
                
                if afectados > 0:
                    await mensaje.reply_text("🗑️ ¡Proyecto eliminado de TiDB y de la web con éxito!")
                else:
                    await mensaje.reply_text("⚠️ Este archivo no estaba registrado en la base de datos.")
            except Exception as e:
                await mensaje.reply_text(f"❌ Error al eliminar: {e}")
        else:
            await mensaje.reply_text("⚠️ El mensaje al que respondiste no contiene un archivo válido.")
    else:
        await mensaje.reply_text("⚠️ Responde al archivo escribiendo /eliminar para borrarlo.")

# 3. Arranque de los Servicios
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
        bot_app.add_handler(CommandHandler("eliminar", eliminar_proyecto))
        bot_app.run_polling()
    except Exception as e:
        print(f"❌ ERROR FATAL AL ARRANCAR TELEGRAM: {e}", flush=True)
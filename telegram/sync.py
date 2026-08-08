import os
import mysql.connector
from telegram import Update
from telegram.ext import Application, MessageHandler, filters, ContextTypes

# Token de tu bot de Telegram
BOT_TOKEN = "8905133806:AAGiteJIjcInIMVgl186C3ouLKbLs3-i4eU"

def guardar_en_tidb(titulo, descripcion, categoria, imagen_url, telegram_file_id):
    try:
        db = mysql.connector.connect(
            host="gateway01.eu-central-1.prod.aws.tidbcloud.com", 
            port=4000,
            user="396CdPhsCTxPorF.root",              
            password="eTQLFL4CRZkoeu1l",         
            database="test",                          
            ssl_disabled=False,
            connect_timeout=10
        )
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

app = Application.builder().token(BOT_TOKEN).build()
app.add_handler(MessageHandler(filters.Document.ALL | filters.VIDEO | filters.AUDIO | filters.PHOTO, manejar_archivo))

if __name__ == "__main__":
    print(">>> Bot de Telegram iniciado de manera independiente...", flush=True)
    app.run_polling()
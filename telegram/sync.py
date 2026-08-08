import os
import mysql.connector
from pyrogram import Client, filters

# 1. Forzar la ruta absoluta para que encuentre la sesión sí o sí
BASE_DIR = os.path.dirname(os.path.abspath(__file__))
SESSION_NAME = os.path.join(BASE_DIR, "mi_sesion")

# 2. Pon tus datos directamente aquí para la prueba local
API_ID = "38390744"        # Pon tu API_ID
API_HASH = "0679d4905087b36cf2f3feaba830c224"    # Pon tu API_HASH

app = Client(SESSION_NAME, api_id=API_ID, api_hash=API_HASH)

def get_db_connection():
    return mysql.connector.connect(
        host=os.environ.get("DB_HOST"), 
        port=4000,
        user=os.environ.get("DB_USER"),              
        password=os.environ.get("DB_PASS"),         
        database=os.environ.get("DB_NAME"),                          
        ssl_disabled=False,
        connect_timeout=10              
    )


@app.on_message(filters.document & filters.caption)
def handle_telegram_upload(client, message):
  print(
      "🔥 ¡ALERTA! El bot detectó un archivo con descripción en el grupo."
  )  # <-- Esto nos dirá si Telegram responde

  try:
    doc = message.document
    file_id = doc.file_id
    caption = message.caption

    db = get_db_connection()
    cursor = db.cursor()

    # Verificar si el archivo ya existe en la base de datos
    cursor.execute(
        "SELECT id FROM projects WHERE telegram_file_id = %s", (file_id,)
    )
    if cursor.fetchone():
      print("ℹ️ El archivo ya está registrado en la base de datos.")
      cursor.close()
      db.close()
      return

    # Valores por defecto
    titulo = "Proyecto sin título"
    categoria = "General"
    descripcion = caption
    imagen_url = "https://via.placeholder.com/400x250?text=Sin+Imagen"

    # Parsear el texto del Caption línea por línea
    lines = caption.split("\n")
    for line in lines:
      if line.lower().startswith("título:") or line.lower().startswith(
          "titulo:"
      ):
        titulo = line.split(":", 1)[1].strip()
      elif line.lower().startswith("categoría:") or line.lower().startswith(
          "categoria:"
      ):
        categoria = line.split(":", 1)[1].strip()
      elif line.lower().startswith("descripción:") or line.lower().startswith(
          "descripcion:"
      ):
        descripcion = line.split(":", 1)[1].strip()
      elif line.lower().startswith("imagen:"):
        imagen_url = line.split(":", 1)[1].strip()

    # Insertar en la base de datos
    query = """
            INSERT INTO projects (titulo, categoria, descripcion, telegram_file_id, imagen_url, fecha_publicacion) 
            VALUES (%s, %s, %s, %s, %s, NOW())
        """
    cursor.execute(
        query, (titulo, categoria, descripcion, file_id, imagen_url)
    )
    db.commit()

    cursor.close()
    db.close()
    print(f"✅ ¡Proyecto sincronizado con éxito en la BD: {titulo}!")

  except Exception as e:
    print(f"❌ ERROR CRÍTICO AL GUARDAR EN DB: {e}")


if __name__ == "__main__":
  print("Iniciando escucha de Telegram...")
  app.run()

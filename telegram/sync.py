import os
import mysql.connector
from pyrogram import Client, filters

# Obtenemos las credenciales desde las variables de entorno de Render
API_ID = os.getenv("API_ID")
API_HASH = os.getenv("API_HASH")
# Usamos la sesión que ya tienes subida (mi_session.session)
SESSION_NAME = "telegram/mi_sesion"
# Inicializamos el cliente de Pyrogram
app = Client(SESSION_NAME, api_id=API_ID, api_hash=API_HASH)


def get_db_connection():
  """Conexión a tu base de datos MySQL"""
  return mysql.connector.connect(
      host=os.getenv("DB_HOST", "localhost"),
      user=os.getenv("DB_USER", "root"),
      password=os.getenv("DB_PASS", ""),
      database=os.getenv("DB_NAME", "tu_base_de_datos"),
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

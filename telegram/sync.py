# telegram/sync.py
import mysql.connector
from pyrogram import Client

# 1. Configuración
API_ID = 38390744 
API_HASH = "0679d4905087b36cf2f3feaba830c224"
GRUPO_ID = -5462752662

app = Client("mi_sesion", api_id=API_ID, api_hash=API_HASH)

async def main():
    # 2. Conectar a MySQL
    db = mysql.connector.connect(
        host="localhost",
        user="root",
        password="",
        database="premium_downloads"
    )
    cursor = db.cursor()

    async with app:
        print("Sincronizando catálogo desde Telegram...")
        
        # 3. Leer historial del grupo
        async for msg in app.get_chat_history(GRUPO_ID):
            # Solo procesar mensajes que tengan un archivo y texto
            if msg.document and msg.caption:
                lineas = msg.caption.split('\n')
                
                # Validar que tenga al menos Título, Categoría y URL
                if len(lineas) >= 3:
                    titulo = lineas[0].strip()
                    categoria = lineas[1].strip()
                    imagen_url = lineas[2].strip()
                    descripcion = '\n'.join(lineas[3:]).strip()
                    file_id = msg.document.file_id
                    msg_id = msg.id
                    fecha = msg.date.strftime('%Y-%m-%d %H:%M:%S')

                    # 4. Insertar o actualizar en MySQL (evita duplicados usando telegram_msg_id)
                    sql = """
                        INSERT INTO projects (telegram_msg_id, titulo, descripcion, categoria, imagen_url, file_id, fecha_publicacion)
                        VALUES (%s, %s, %s, %s, %s, %s, %s)
                        ON DUPLICATE KEY UPDATE 
                        titulo=VALUES(titulo), descripcion=VALUES(descripcion), 
                        categoria=VALUES(categoria), imagen_url=VALUES(imagen_url), file_id=VALUES(file_id)
                    """
                    try:
                        cursor.execute(sql, (msg_id, titulo, descripcion, categoria, imagen_url, file_id, fecha))
                        db.commit()
                        print(f"✅ Proyecto sincronizado: {titulo}")
                    except Exception as e:
                        print(f"❌ Error con {titulo}: {e}")
                        
        print("¡Sincronización terminada!")
        db.close()

if __name__ == "__main__":
    app.run(main())
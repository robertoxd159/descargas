# telegram/download.py
import sys
from pyrogram import Client

# 1. Configuración (¡Pon tus datos!)
API_ID = 38390744 
API_HASH = "0679d4905087b36cf2f3feaba830c224"

# 2. Recibir instrucciones de PHP
file_id = sys.argv[1]
output_file = sys.argv[2]

app = Client("mi_sesion", api_id=API_ID, api_hash=API_HASH)

async def main():
    async with app:
        # Descargar el archivo exacto y guardarlo en la ruta temporal
        await app.download_media(message=file_id, file_name=output_file)

if __name__ == "__main__":
    app.run(main())
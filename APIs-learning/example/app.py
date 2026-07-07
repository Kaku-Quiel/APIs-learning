from flask import Flask, request, jsonify

# 1. Crear la aplicación (el "hotel")
app = Flask(__name__)

# 2. Base de datos simulada (en memoria)
libros = [
    {"id": 1, "titulo": "Leyenda andante", "autor": "Pedro", "estado": "disponible"},
    {"id": 2, "titulo": "El principito", "autor": "Saint-Exupéry", "estado": "prestado"},
    {"id": 3, "titulo": "1984", "autor": "George Orwell", "estado": "disponible"}
]

# 3. Endpoint GET /libros
@app.route('/libros', methods=['GET'])
def listar_libros():
    # Devuelve la lista completa de libros en formato JSON
    return jsonify(libros)

# 4. Endpoint GET /libros/<id>
@app.route('/libros/<int:id>', methods=['GET'])
def obtener_libro(id):
    # Buscar el libro por ID
    for libro in libros:
        if libro['id'] == id:
            return jsonify(libro)
    # Si no existe, devolver error 404
    return jsonify({"error": "Libro no encontrado"}), 404

# 5. Endpoint POST /libros
@app.route('/libros', methods=['POST'])
def crear_libro():
    # Obtener los datos enviados por el cliente (en JSON)
    datos = request.get_json()
    
    # Crear un nuevo libro con un ID automático
    nuevo_id = max(libro['id'] for libro in libros) + 1
    nuevo_libro = {
        "id": nuevo_id,
        "titulo": datos['titulo'],
        "autor": datos['autor'],
        "estado": "disponible"  # Siempre disponible al crearlo
    }
    libros.append(nuevo_libro)
    print(nuevo_libro)
    
    # Devolver el libro creado con código 201 (creado)
    return jsonify(nuevo_libro), 201

# 6. Endpoint PUT /libros/<id>/prestar
@app.route('/libros/<int:id>/prestar', methods=['PUT'])
def prestar_libro(id):
    # Buscar el libro
    for libro in libros:
        if libro['id'] == id:
            if libro['estado'] == 'prestado':
                return jsonify({"error": "El libro ya está prestado"}), 400
            # Cambiar estado
            libro['estado'] = 'prestado'
            return jsonify({"mensaje": "Libro prestado exitosamente", "libro": libro})
    
    return jsonify({"error": "Libro no encontrado"}), 404

# 7. Iniciar el servidor (solo si se ejecuta este archivo)

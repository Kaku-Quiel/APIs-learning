from flask import Flask, request, jsonify

app = Flask(__name__)

tareas = [
    {"id": 1, "titulo": "matematicas", "completada": False},
    {"id": 2, "titulo": "sociales", "completada": True}
]
next_id = 3  # Controlador de ID manual

@app.route("/tareas", methods=['GET'])
def get_tareas():
    return jsonify(tareas), 200

@app.route("/tareas/<int:id>", methods=['GET'])
def get_tarea(id):
    for tarea in tareas:
        if tarea['id'] == id:
            return jsonify(tarea), 200
    return jsonify({"error": "Tarea no encontrada"}), 404

@app.route("/tareas", methods=['POST'])
def create_tarea():
    data = request.get_json()
    if not data or 'titulo' not in data:
        return jsonify({"error": "El campo 'titulo' es obligatorio"}), 400
    
    global next_id
    nueva_tarea = {
        "id": next_id,
        "titulo": data['titulo'],
        "completada": data.get('completada', False)  # Si no se envía, por defecto False
    }
    next_id += 1
    tareas.append(nueva_tarea)
    return jsonify(nueva_tarea), 201

@app.route("/tareas/<int:id>", methods=['PUT'])
def update_tarea(id):
    data = request.get_json()
    for tarea in tareas:
        if tarea['id'] == id:
            # Actualizar solo los campos que se envían
            if 'titulo' in data:
                tarea['titulo'] = data['titulo']
            if 'completada' in data:
                tarea['completada'] = data['completada']  # Es booleano directamente
            return jsonify(tarea), 200
    return jsonify({"error": "Tarea no encontrada"}), 404

@app.route("/tareas/<int:id>", methods=['DELETE'])
def delete_tarea(id):
    for tarea in tareas:
        if tarea['id'] == id:
            tareas.remove(tarea)
            return '', 204  # 204 No Content, sin cuerpo
    return jsonify({"error": "Tarea no encontrada"}), 404

if __name__ == '__main__':
    app.run(debug=True, port=5000)
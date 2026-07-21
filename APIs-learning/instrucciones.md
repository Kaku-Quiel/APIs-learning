# 📝 Gestor de Tareas — Full Stack

**Frontend en HTML/JS puro + API REST en Python (Flask)**

---

## 💬 Pedido del cliente

> "Hola, ya sé que sabes crear una API REST porque hiciste la de películas. Ahora necesito algo más completo: quiero una página web real que hable con un servidor, no solo la API sola probada con curl."
>
> "Necesito un **gestor de tareas personal**: una lista de pendientes donde pueda agregar tareas, marcarlas como completadas, editarlas, eliminarlas y filtrarlas. Todo debe verse en el navegador y actualizarse sin recargar la página."
>
> "No me importa que sea feo, **no quiero que uses CSS** — solo HTML plano y JavaScript (o TypeScript si prefieres). El servidor debe ser Python con Flask, guardando los datos en memoria. Te dejo la especificación completa a continuación."

---

## 🎯 El proyecto

Vas a construir una aplicación **full stack** compuesta por dos partes independientes que se comunican mediante HTTP:

- **Backend:** una API REST en **Python + Flask** que expone los datos de las tareas.
- **Frontend:** una página en **HTML puro** (sin ningún archivo `.css`) que usa **JavaScript o TypeScript** para consumir esa API con `fetch` y actualizar el DOM dinámicamente.

La diferencia clave con tu ejercicio anterior es que ahora el frontend y el backend son **dos programas separados** que deben coordinarse: el navegador le pide datos al servidor, el servidor responde en JSON, y tu JavaScript transforma esa respuesta en elementos visibles en la página.

> 🎯 **Sin soluciones:** Este documento define la especificación exacta, pero **tú escribes todo el código**, tanto del servidor como del cliente. Así se aprende de verdad.

---

## 🧩 Stack técnico obligatorio

| Parte | Tecnología | Restricciones |
|---|---|---|
| Backend | Python 3 + Flask | Datos en memoria (una lista/diccionario en el servidor), sin base de datos |
| Frontend | HTML puro | Cero CSS. Nada de estilos, ni inline ni en archivo aparte |
| Lógica de cliente | JavaScript o TypeScript | Debe usar `fetch` para hablar con la API. Si usas TypeScript, debes compilarlo a JS antes de servirlo |

Estructura de carpetas sugerida (organízala como prefieras, esto es solo una guía):

```
proyecto-tareas/
├── backend/
│   └── app.py          # servidor Flask
└── frontend/
    ├── index.html       # página sin CSS
    └── app.js            # o app.ts si usas TypeScript
```

> 💡 **Sobre CORS:** Como el HTML y la API corren en puertos distintos (por ejemplo `file://` o un servidor estático en el 5500, y Flask en el 5000), es muy probable que necesites investigar y habilitar **CORS** en Flask para que el navegador no bloquee las peticiones. Esto es parte del ejercicio: investígalo tú.

---

## 📋 Estructura de una Tarea

Cada tarea en tu sistema debe tener estos campos mínimos:

```jsonc
{
  "id": // entero único, generado automáticamente por el servidor
  "titulo": // texto (obligatorio, no vacío)
  "descripcion": // texto (opcional, puede ir vacío)
  "prioridad": // "baja" | "media" | "alta" (obligatorio)
  "completada": // booleano (por defecto false)
  "fecha_creacion": // fecha/hora generada por el servidor al crearla
}
```

Ejemplo concreto de una tarea:

```json
{
  "id": 1,
  "titulo": "Repasar Flask",
  "descripcion": "Ver documentación de rutas dinámicas",
  "prioridad": "alta",
  "completada": false,
  "fecha_creacion": "2026-07-21T10:30:00"
}
```

---

## 🔌 Endpoints que debe exponer el backend

### 1. Obtener todas las tareas `GET`
- **Ruta:** `/api/tareas`
- **Respuesta:** `200 OK` con un arreglo JSON de todas las tareas.
- **Extra opcional:** filtrar con query params, por ejemplo `?completada=true` o `?prioridad=alta`.

### 2. Obtener una tarea por ID `GET`
- **Ruta:** `/api/tareas/{id}`
- **Respuesta exitosa:** `200 OK` con el objeto tarea.
- **Error 404:** `{"error": "Tarea no encontrada"}` si no existe.

### 3. Crear una tarea `POST`
- **Ruta:** `/api/tareas`
- **Cuerpo JSON:** `titulo` y `prioridad` son obligatorios. `descripcion` es opcional. `completada` siempre inicia en `false`.
- **Validaciones:**
  - El `titulo` no puede estar vacío.
  - La `prioridad` debe ser exactamente `"baja"`, `"media"` o `"alta"`.
- **Respuesta exitosa:** `201 Created` con la tarea recién creada, incluyendo su `id` y `fecha_creacion`.
- **Error de validación:** `400 Bad Request` con un mensaje descriptivo.

### 4. Actualizar una tarea `PUT`
- **Ruta:** `/api/tareas/{id}`
- **Cuerpo JSON:** cualquier campo editable (`titulo`, `descripcion`, `prioridad`, `completada`). Solo se modifican los campos enviados.
- **Respuesta:** `200 OK` con la tarea actualizada, o `404` si no existe.

### 5. Eliminar una tarea `DELETE`
- **Ruta:** `/api/tareas/{id}`
- **Respuesta:** `204 No Content`, o `404` si no existe.

### 6. Marcar como completada/pendiente `PATCH`
- **Ruta:** `/api/tareas/{id}/completar`
- **Comportamiento:** invierte el valor actual de `completada` (si estaba en false pasa a true, y viceversa).
- **Respuesta:** `200 OK` con la tarea actualizada.

> 💡 **Pista:** Igual que en tu API anterior, mantén un contador de ID incremental en el servidor.

---

## 🖥️ Qué debe hacer la página (frontend)

Tu `index.html` debe consumir la API anterior con `fetch` y ofrecer, sin recargar la página en ningún momento, lo siguiente:

### 1. Listar tareas — *al cargar*
- Al abrir la página, debe pedir `GET /api/tareas` y mostrar cada tarea en la pantalla (por ejemplo, un elemento por tarea con su título, prioridad y estado).
- Las tareas completadas deben distinguirse visualmente de alguna forma *usando solo HTML* (por ejemplo con la etiqueta `<s>` para tachar el texto, o mostrando la palabra "(completada)").

### 2. Formulario para crear tareas — *interacción*
- Un formulario con campos para título, descripción y prioridad (puedes usar un `<select>` para prioridad).
- Al enviarlo, debe hacer `POST /api/tareas` y agregar la nueva tarea a la lista visible **sin recargar la página**.
- Si el backend responde con error 400, debes mostrar ese mensaje de error en la página.

### 3. Marcar como completada — *interacción*
- Cada tarea debe tener un botón o checkbox para marcarla como completada/pendiente, que dispare la petición `PATCH` correspondiente y actualice esa tarea en pantalla.

### 4. Eliminar tareas — *interacción*
- Un botón por tarea que la elimine llamando a `DELETE` y la quite de la lista visible.

### 5. Filtrar tareas — *opcional recomendado*
- Algún control (botones o un `<select>`) para mostrar solo pendientes, solo completadas, o filtrar por prioridad, reutilizando los query params del endpoint `GET /api/tareas`.

> ⚠️ **Recuerda:** ningún archivo `.css`, ningún atributo `style`, ninguna etiqueta `<style>`. Todo el orden visual que consigas debe venir de HTML semántico (listas, tablas, encabezados, etc). Lo importante aquí es la lógica, no el diseño.

---

## 🔄 Cómo deben hablar frontend y backend

Este es el ciclo que debe repetirse cada vez que el usuario interactúa con la página:

1. El usuario hace algo en el HTML (llena un formulario, hace clic en un botón).
2. Tu JavaScript captura ese evento y arma la petición `fetch` correspondiente (método, URL, cuerpo JSON si aplica).
3. El servidor Flask recibe la petición, valida, modifica los datos en memoria y responde en JSON.
4. Tu JavaScript recibe la respuesta, y actualiza el DOM para reflejar el nuevo estado — sin recargar la página.

---

## 🏁 Cómo empezar

1. Crea el backend primero: define la lista de tareas en memoria y monta las rutas CRUD básicas.
2. Prueba el backend **solo con curl o Postman** antes de tocar el frontend (así aíslas errores).
3. Instala y configura CORS en Flask para permitir peticiones desde tu HTML.
4. Crea el `index.html` con la estructura básica (lista vacía, formulario) y sirve ese archivo con un servidor estático simple (por ejemplo la extensión Live Server, o `python -m http.server` en otra carpeta/puerto).
5. Escribe el JavaScript que hace `fetch` a `GET /api/tareas` y pinta la lista al cargar la página.
6. Añade, uno por uno, crear → completar → eliminar → filtrar.

---

## 🧪 Prueba tu API antes de conectarla al frontend

Con tu servidor Flask corriendo (por ejemplo en `http://localhost:5000`):

```bash
# Obtener todas las tareas
curl http://localhost:5000/api/tareas

# Crear una tarea
curl -X POST http://localhost:5000/api/tareas \
  -H "Content-Type: application/json" \
  -d '{"titulo":"Estudiar CORS","prioridad":"alta","descripcion":"Antes de conectar el frontend"}'

# Marcar como completada
curl -X PATCH http://localhost:5000/api/tareas/1/completar

# Filtrar por prioridad
curl "http://localhost:5000/api/tareas?prioridad=alta"

# Eliminar
curl -X DELETE http://localhost:5000/api/tareas/1
```

---

## 🚀 Retos extra (para mentes curiosas)

- **TypeScript:** escribe todo el frontend en `.ts` y compílalo con `tsc` a JS plano antes de servirlo.
- **Persistencia en archivo:** guarda las tareas en un `tareas.json` y recárgalas al iniciar el servidor.
- **Edición in-line:** permite editar el título de una tarea directamente en la lista (usando `PUT`), sin un formulario aparte.
- **Búsqueda:** un campo de texto que filtre las tareas visibles por coincidencia en el título, sin recargar.
- **Contador dinámico:** muestra en la página cuántas tareas hay pendientes vs. completadas, actualizado en tiempo real.
- **Manejo de errores de red:** si el `fetch` falla (servidor apagado), muestra un mensaje claro en la página en vez de que no pase nada.

---

## ✅ ¿Qué aprenderás con este ejercicio?

- Separar responsabilidades entre un backend y un frontend independientes.
- Consumir una API REST desde el navegador usando `fetch` y promesas/async-await.
- Manipular el DOM dinámicamente a partir de datos JSON.
- Manejar CORS, un problema real de cualquier proyecto full stack.
- Pensar en el ciclo completo petición → respuesta → actualización de interfaz.

Este es el primer paso hacia construir aplicaciones web reales. ¡A programar! 📝

---

*Pedido de cliente diseñado para aprender construyendo · No copies, crea*

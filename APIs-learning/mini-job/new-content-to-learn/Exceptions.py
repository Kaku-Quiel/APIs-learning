class EdadInvalidaError(Exception):
  pass

def validar(edad):
  if edad < 0 or edad > 120:
    raise EdadInvalidaError("Edad fuera del rango 0 - 120")
  

while True:
  try:
    edad = int(input("Edad: "))
    validar(edad)
    print(f"Edad valida: {edad}\n\n")

  except EdadInvalidaError as e:
    print(f"Error: {e}")
    break

  except ValueError:
    print(f"Error: No puedes poner un texto")
    break

  except Exception as e:
    print(f"Not excpeted Error: {e.__class__} - {e}")
    break

print("\n\nGracias por usar el sistema")



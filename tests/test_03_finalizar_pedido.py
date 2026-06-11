from selenium import webdriver
from selenium.webdriver.edge.options import Options as EdgeOptions
from selenium.webdriver.common.by import By
from selenium.webdriver.support.ui import WebDriverWait
from selenium.webdriver.support import expected_conditions as EC
import time


class TestFinalizarPedido:

    def setup_method(self):
        options = EdgeOptions()
        options.add_experimental_option(
            "prefs",
            {
                "credentials_enable_service": False,
                "profile.password_manager_enabled": False
            }
        )
        options.add_argument("--disable-popup-blocking")
        options.add_argument("--start-maximized")

        self.driver = webdriver.Edge(options=options)
        self.wait = WebDriverWait(self.driver, 20)

    def teardown_method(self):
        self.driver.quit()

    def test_finalizar_pedido(self):

        # =====================================================
        # LOGIN CLIENTE
        # =====================================================
        self.driver.get("http://localhost/sistema_Pollos/public/index.php")

        header_host = self.wait.until(
            EC.presence_of_element_located((By.TAG_NAME, "header-component"))
        )

        btn_ingresar = self.driver.execute_script(
            "return arguments[0].shadowRoot.querySelector('.login-btn')",
            header_host
        )
        btn_ingresar.click()

        self.wait.until(EC.presence_of_element_located((By.ID, "correo")))
        self.driver.find_element(By.ID, "correo").send_keys("cliente@example.com")
        self.driver.find_element(By.ID, "contrasena").send_keys("12345678")
        self.driver.find_element(By.ID, "btnLogin").click()

        self.wait.until(EC.url_contains("index.php"))
        print("\n++++ LOGIN EXITOSO")

        # =====================================================
        # IR AL CATÁLOGO DESDE EL INDEX
        # =====================================================
        btn_ordenar = self.wait.until(
            EC.presence_of_element_located(
                (By.XPATH, "(//button[contains(.,'Ordenar Ahora')])[1]")
            )
        )

        self.driver.execute_script("arguments[0].scrollIntoView(true);", btn_ordenar)
        self.driver.execute_script("arguments[0].click();", btn_ordenar)

        self.wait.until(EC.url_contains("catalogo.php"))
        print("++++ CATÁLOGO ABIERTO")

        # =====================================================
        # AGREGAR PRODUCTO
        # =====================================================
        btn_agregar = self.wait.until(
            EC.element_to_be_clickable(
                (By.XPATH, "(//button[contains(.,'Añadir al carrito')])[1]")
            )
        )
        btn_agregar.click()

        mensaje_carrito = self.wait.until(
            EC.visibility_of_element_located((By.CLASS_NAME, "alerta-exito"))
        )
        print(f"++++ MENSAJE: {mensaje_carrito.text}")

        # =====================================================
        # ABRIR CARRITO DESDE HEADER (SHADOW DOM)
        # =====================================================
        header_host = self.wait.until(
            EC.presence_of_element_located((By.TAG_NAME, "header-component"))
        )

        link_carrito = self.driver.execute_script(
            "return arguments[0].shadowRoot.querySelector('a[title=\"Carrito\"]')",
            header_host
        )
        link_carrito.click()

        self.wait.until(EC.url_contains("carritoCliente"))
        print("++++ CARRITO ABIERTO")

        # =====================================================
        # FINALIZAR PEDIDO Y CONFIRMAR ALERTA
        # =====================================================
        btn_finalizar = self.wait.until(
            EC.element_to_be_clickable((By.ID, "btnFinalizar"))
        )
        btn_finalizar.click()
        print("++++ CLICK EN FINALIZAR PEDIDO")

        alerta = self.wait.until(EC.alert_is_present())
        print(f"++++ ALERTA INTERCEPTADA: {alerta.text}")
        alerta.accept()
        print("++++ ALERTA ACEPTADA CON ÉXITO")

        # =====================================================
        # CORRECCIÓN EN LA VALIDACIÓN: VALIDAR REDIRECCIÓN AL HISTORIAL
        # =====================================================
        # Esperamos a que el sistema nos redirija automáticamente a las vistas del cliente
        self.wait.until(EC.url_contains("pedidosCliente"))
        print("++++ REDIRECCIÓN AUTOMÁTICA DETECTADA: El usuario llegó al Historial.")

        # Validamos que el título del contenedor del historial sea visible en la pantalla
        titulo_historial = self.wait.until(
            EC.visibility_of_element_located(
                (By.XPATH, "//*[contains(text(),'Historial de Pedidos')]")
            )
        )

        actual = titulo_historial.text
        esperado = "Historial de Pedidos"

        print(f"++++ ACTUAL CAPTURADO EN PANTALLA: {actual}")

        # Assert final robusto basado en el cambio de estado de la aplicación
        assert esperado in actual, (
            f"Error: Se esperaba llegar a '{esperado}', "
            f"pero el texto en pantalla es '{actual}'"
        )
        
        # Pausa opcional de cortesía para ver el resultado en el navegador antes de cerrar
        time.sleep(3)
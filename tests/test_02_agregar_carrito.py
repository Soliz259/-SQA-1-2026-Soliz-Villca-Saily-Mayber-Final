from selenium import webdriver
from selenium.webdriver.edge.options import Options as EdgeOptions
from selenium.webdriver.common.by import By
from selenium.webdriver.support.ui import WebDriverWait
from selenium.webdriver.support import expected_conditions as EC
import time


class TestAgregarCarrito:

    def setup_method(self):
        # Configuración exclusiva para Microsoft Edge
        options = EdgeOptions()
        
        # Desactivar ventanas emergentes y gestores de credenciales en Edge
        options.add_experimental_option(
            "prefs",
            {
                "credentials_enable_service": False,
                "profile.password_manager_enabled": False
            }
        )
        # Evita que bloqueos de alertas de seguridad detengan el click
        options.add_argument("--disable-popup-blocking")
        options.add_argument("--start-maximized")

        self.driver = webdriver.Edge(options=options)
        self.wait = WebDriverWait(self.driver, 20)

    def teardown_method(self):
        self.driver.quit()

    def test_agregar_producto_carrito(self):

        # =====================================================
        # LOGIN CLIENTE (Flujo inicial obligatorio)
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
        
       
        time.sleep(2)

        # =====================================================
        # NAVEGACIÓN AL CATÁLOGO EVÍA CLIC (Usando scroll seguro)
        # =====================================================
        # Buscamos el primer botón que contenga el texto "Ordenar Ahora"
        btn_ordenar_ahora = self.wait.until(
            EC.presence_of_element_located(
                (By.XPATH, "(//button[contains(., 'Ordenar Ahora')])[1]")
            )
        )
        
        
       
        self.driver.execute_script("arguments[0].scrollIntoView(true);", btn_ordenar_ahora)
        self.driver.execute_script("arguments[0].click();", btn_ordenar_ahora)
        print("\n++++ PASO: Clic seguro en 'Ordenar Ahora' del index exitoso.")

        
        self.wait.until(EC.url_contains("catalogo.php"))

        
        self.wait.until(
            EC.visibility_of_element_located(
                (By.XPATH, "//*[contains(text(),'Nuestro')]")
            )
        )

        # =====================================================
        # AGREGAR PRIMER PRODUCTO EN EL CATÁLOGO
        # =====================================================
        primer_boton = self.wait.until(
            EC.element_to_be_clickable(
                (By.XPATH, "(//button[contains(.,'Añadir al carrito')])[1]")
            )
        )
        primer_boton.click()
        print("++++ PASO: Clic en 'Añadir al carrito' exitoso.")

        # =====================================================
        # VALIDAR MENSAJE DE ÉXITO
        # =====================================================
        mensaje = self.wait.until(
            EC.visibility_of_element_located(
                (By.CLASS_NAME, "alerta-exito")
            )
        )

        actual = mensaje.text
        esperado = "Producto agregado al carrito."

        print(f"++++ ACTUAL CAPTURADO: {actual}")

        assert esperado in actual, (
            f"Error: se esperaba '{esperado}' "
            f"pero se obtuvo '{actual}'"
        )
from selenium import webdriver
from selenium.webdriver.common.by import By
from selenium.webdriver.support.ui import WebDriverWait
from selenium.webdriver.support import expected_conditions as EC


class TestLoginCliente:

    def setup_method(self):
        self.driver = webdriver.Chrome()
        self.driver.maximize_window()
        self.wait = WebDriverWait(self.driver, 20)

    def teardown_method(self):
        self.driver.quit()

    def test_login_cliente_exitoso(self):

        # 1. Abrir página principal
        self.driver.get("http://localhost/sistema_Pollos/public/index.php")

        # =========================================================================
        # CORRECCIÓN AQUÍ: Penetración de Shadow DOM para el botón 'Ingresar'
        # =========================================================================
        # Primero localizamos el elemento contenedor/padre en el DOM principal
        header_host = self.wait.until(
            EC.presence_of_element_located((By.TAG_NAME, "header-component"))
        )
        
        # Ejecutamos JavaScript para extraer el elemento interno con la clase .login-btn
        js_buscar_boton = "return arguments[0].shadowRoot.querySelector('.login-btn')"
        btn_ingresar = self.driver.execute_script(js_buscar_boton, header_host)
        
        # Validamos que JS haya encontrado exitosamente el botón antes de clickearlo
        assert btn_ingresar is not None, "Error: No se pudo localizar '.login-btn' dentro del Shadow DOM"
        btn_ingresar.click()
        # =========================================================================

        # 2. Verificar que llegó al login
        self.wait.until(
            EC.presence_of_element_located((By.ID, "correo"))
        )

        # 3. Credenciales del cliente
        self.driver.find_element(By.ID, "correo").send_keys("cliente@example.com")
        self.driver.find_element(By.ID, "contrasena").send_keys("12345678")

        # 4. Enviar formulario
        self.driver.find_element(By.ID, "btnLogin").click()

        # 5. Esperar a que vuelva al index despues de autenticarse
        self.wait.until(
            EC.url_contains("index.php")
        )

        # 6. Verificar que se muestra contenido del inicio con sesión activa
        favorito = self.wait.until(
            EC.visibility_of_element_located(
                (By.XPATH, "//*[contains(text(),'Nuestros Favoritos')]")
            )
        )

        actual = favorito.text
        esperado = "Nuestros Favoritos"

        print(f"\n++++ ACTUAL CAPTURADO: {actual}")

        assert actual == esperado, (
            f"Error: se esperaba '{esperado}' "
            f"pero se obtuvo '{actual}'"
        )
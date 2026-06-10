class HeaderComponent extends HTMLElement {
  constructor() {
    super();
    this.attachShadow({ mode: 'open' });
    this.enlacePanel = '';
    this.mostrarCarrito = false;
  }

  async connectedCallback() {
    await this.loadSessionData();
    this.render();
    this.setupEventListeners();
  }

  async loadSessionData() {
    try {
      const response = await fetch("estadoSesion.php");
      const data = await response.json();

      if (data.logeado) {
        this.enlacePanel = this.getPanelLink(data.rol);
        this.mostrarCarrito = (data.rol === 2);
      }
    } catch (error) {
      console.error("Error loading session data:", error);
    }
  }

  getPanelLink(rol) {
    switch (Number(rol)) {
      case 1: return "Views/admin/panelAdmin.html";
      case 2: return "Views/client/panelCliente.html";
      case 3: return "Views/repartidor/panel_repartidor.html";
      default: return "index.php";
    }
  }


  render() {
    this.shadowRoot.innerHTML = `
      <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap');
        
        :host {
          display: block;
          background-color: var(--negro, #000000);
          color: white;
          padding: 2rem;
          box-shadow: 0 2px 5px rgba(0,0,0,0.1);
          font-family: 'Poppins', sans-serif;
        }
        
        .header-container {
          display: flex;
          justify-content: space-between;
          align-items: center;
          max-width: 1200px;
          margin: 0 auto;
          gap: 1rem;
        }
        
        .logo {
          display: flex;
          align-items: center;
          text-decoration: none;
          min-width: max-content;
        }
        
        .logo-text {
          display: flex;
          white-space: nowrap;
        }
        
        .logo-pollos {
          color: var(--blanco, #FFFFFF);
          font-weight: 600;
        }
        
        .logo-express {
          color: var(--amarillo, #F4A300);
          font-weight: 600;
        }
        
        .nav-links {
          display: flex;
          gap: 1rem;
          align-items: center;
          flex-wrap: wrap;
          justify-content: flex-end;
        }
        
        .nav-links a {
          color: white;
          text-decoration: none;
          transition: color 0.3s;
          font-weight: 400;
          display: flex;
          align-items: center;
          gap: 0.3rem;
          white-space: nowrap;
        }
        
        .nav-links a:hover {
          color: var(--amarillo, #F4A300);
        }
        
        .icon {
          width: 1.2em;
          height: 1.2em;
          fill: currentColor;
          flex-shrink: 0;
        }
        
        .user-menu {
          position: relative;
          cursor: pointer;
          display: flex;
          align-items: center;
          gap: 0.3rem;
        }
        
        .dropdown {
          position: absolute;
          right: 0;
          top: 100%;
          background: white;
          border-radius: 4px;
          box-shadow: 0 2px 10px rgba(0,0,0,0.1);
          display: none;
          flex-direction: column;
          min-width: 180px;
          z-index: 100;
          overflow: hidden;
        }
        
        .dropdown a {
          color: #333 !important;
          padding: 0.5rem 1rem;
          display: flex;
          align-items: center;
          gap: 0.5rem;
        }
        
        .dropdown a:hover {
          background: #f5f5f5;
        }
        
        .carrito-contador {
          background: var(--amarillo, #F4A300);
          color: var(--negro, #000000);
          border-radius: 50%;
          padding: 0.1em 0.4em;
          font-size: 0.7rem;
          font-weight: 600;
        }
        
        .login-btn {
          background: var(--amarillo, #F4A300);
          color: var(--negro, #000000);
          padding: 0.4rem 0.8rem;
          border-radius: 4px;
          font-weight: 600;
          transition: all 0.3s;
          display: flex;
          align-items: center;
          gap: 0.3rem;
          font-size: 0.9rem;
        }
        
        .login-btn:hover {
          background: var(--hover-amarillo, #FFC933);
        }
        
        /* Responsive para tablets */
        @media (max-width: 768px) {
          :host {
            padding: 0.8rem;
          }
          
          .logo-pollos, .logo-express {
            font-size: 1rem;
          }
          
          .nav-links {
            gap: 0.8rem;
          }
          
          .nav-links a {
            font-size: 0.9rem;
          }
        }
        
        /* Responsive para móviles pequeños */
        @media (max-width: 480px) {
          .header-container {
            flex-wrap: wrap;
          }
          
          .logo {
            order: 1;
          }
          
          .nav-links {
            order: 3;
            width: 100%;
            justify-content: space-around;
            margin-top: 0.5rem;
          }
          
          .login-btn {
            order: 2;
            margin-left: auto;
          }
          
          .logo-text {
            font-size: 0.9rem;
          }
        }
      </style>
      
      <div class="header-container">
        <a href="index.php" class="logo">
          <div class="logo-text">
            <span class="logo-pollos">Pollos</span><span class="logo-express">Express</span>
          </div>
        </a>
        
        <div class="nav-links">
          ${this.renderNavigation()}
        </div>
      </div>
    `;
  }

  renderNavigation() {
    if (this.enlacePanel) {
      return `
        ${this.mostrarCarrito ? `
          <a href="views/client/carritoCliente.html" title="Carrito">
            <svg class="icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16" fill="currentColor">
              <path d="M0 1.5A.5.5 0 0 1 .5 1H2a.5.5 0 0 1 .485.379L2.89 3H14.5a.5.5 0 0 1 .491.592l-1.5 8A.5.5 0 0 1 13 12H4a.5.5 0 0 1-.491-.408L2.01 3.607 1.61 2H.5a.5.5 0 0 1-.5-.5zM5 12a2 2 0 1 0 0 4 2 2 0 0 0 0-4zm7 0a2 2 0 1 0 0 4 2 2 0 0 0 0-4zm-7 1a1 1 0 1 1 0 2 1 1 0 0 1 0-2zm7 0a1 1 0 1 1 0 2 1 1 0 0 1 0-2z"/>
            </svg>
            <span class="carrito-contador">0</span>
          </a>
        ` : ''}
        <div class="user-menu">
          <svg class="icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16" fill="currentColor">
            <path d="M8 8a3 3 0 1 0 0-6 3 3 0 0 0 0 6zm2-3a2 2 0 1 1-4 0 2 2 0 0 1 4 0zm4 8c0 1-1 1-1 1H3s-1 0-1-1 1-4 6-4 6 3 6 4zm-1-.004c-.001-.246-.154-.986-.832-1.664C11.516 10.68 10.289 10 8 10c-2.29 0-3.516.68-4.168 1.332-.678.678-.83 1.418-.832 1.664h10z"/>
          </svg>
          <span>Cuenta</span>
          <div class="dropdown">
            <a href="${this.enlacePanel}">
              <svg class="icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16" fill="currentColor">
                <path fill-rule="evenodd" d="M15 16s1 0 1-1-1-4-5-4-5 3-5 4 1 1 1 1h8zm-7.978-1h7.956a.274.274 0 0 0 .014-.002l.008-.002c-.002-.264-.167-1.03-.76-1.72C13.688 10.629 12.718 10 11 10c-1.717 0-2.687.63-3.24 1.276-.593.69-.759 1.457-.76 1.72a1.05 1.05 0 0 0 .022.004zM11 7a2 2 0 1 0 0-4 2 2 0 0 0 0 4zm3-2a3 3 0 1 1-6 0 3 3 0 0 1 6 0zM6.936 9.28a5.88 5.88 0 0 0-1.23-.247A7.35 7.35 0 0 0 5 9c-4 0-5 3-5 4 0 .667.333 1 1 1h4.216A2.238 2.238 0 0 1 5 13c0-1.01.377-2.042 1.09-2.904.243-.294.526-.569.846-.816zM4.92 10A5.493 5.493 0 0 0 4 13H1c0-.26.164-1.03.76-1.724.545-.636 1.492-1.256 3.16-1.275zM7 5a2 2 0 1 1-4 0 2 2 0 0 1 4 0zm3 0a3 3 0 1 1-6 0 3 3 0 0 1 6 0z"/>
              </svg>
              Panel
            </a>
            <a href="index" class="logout-btn">
              <svg class="icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16" fill="currentColor">
                <path fill-rule="evenodd" d="M6 3.5a.5.5 0 0 1 .5-.5h8a.5.5 0 0 1 .5.5v9a.5.5 0 0 1-.5.5h-8a.5.5 0 0 1-.5-.5v-2a.5.5 0 0 0-1 0v2A1.5 1.5 0 0 0 6.5 14h8a1.5 1.5 0 0 0 1.5-1.5v-9A1.5 1.5 0 0 0 14.5 2h-8A1.5 1.5 0 0 0 5 3.5v2a.5.5 0 0 0 1 0v-2z"/>
                <path fill-rule="evenodd" d="M11.854 8.354a.5.5 0 0 0 0-.708l-3-3a.5.5 0 1 0-.708.708L10.293 7.5H1.5a.5.5 0 0 0 0 1h8.793l-2.147 2.146a.5.5 0 0 0 .708.708l3-3z"/>
              </svg>
              Salir
            </a>
          </div>
        </div>
      `;
    } else {
      return `
        <a href="login.php" class="login-btn">
          <svg class="icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16" fill="currentColor">
            <path fill-rule="evenodd" d="M6 3.5a.5.5 0 0 1 .5-.5h8a.5.5 0 0 1 .5.5v9a.5.5 0 0 1-.5.5h-8a.5.5 0 0 1-.5-.5v-2a.5.5 0 0 0-1 0v2A1.5 1.5 0 0 0 6.5 14h8a1.5 1.5 0 0 0 1.5-1.5v-9A1.5 1.5 0 0 0 14.5 2h-8A1.5 1.5 0 0 0 5 3.5v2a.5.5 0 0 0 1 0v-2z"/>
            <path fill-rule="evenodd" d="M11.854 8.354a.5.5 0 0 0 0-.708l-3-3a.5.5 0 1 0-.708.708L10.293 7.5H1.5a.5.5 0 0 0 0 1h8.793l-2.147 2.146a.5.5 0 0 0 .708.708l3-3z"/>
          </svg>
          Ingresar
        </a>
      `;
    }
  }

  setupEventListeners() {
    const userMenu = this.shadowRoot.querySelector('.user-menu');
    if (userMenu) {
      userMenu.addEventListener('click', (e) => {
        e.stopPropagation();
        const dropdown = this.shadowRoot.querySelector('.dropdown');
        dropdown.style.display = dropdown.style.display === 'flex' ? 'none' : 'flex';
      });
    }

    const logoutBtn = this.shadowRoot.querySelector('.logout-btn');
    if (logoutBtn) {
      logoutBtn.addEventListener('click', (e) => {
        e.preventDefault();
        window.location.href = "logout.php";
      });
    }

    // Cerrar dropdown al hacer clic fuera
    document.addEventListener('click', () => {
      const dropdown = this.shadowRoot.querySelector('.dropdown');
      if (dropdown) dropdown.style.display = 'none';
    });

    if (this.mostrarCarrito) {
      this.updateCart();
    }
  }

  async updateCart() {
    try {
      const response = await fetch("api/ver-carrito");
      const data = await response.json();
      const cantidad = data?.productos?.length || 0;
      const contador = this.shadowRoot.querySelector('.carrito-contador');
      if (contador) contador.textContent = cantidad;
    } catch (error) {
      console.error("Error updating cart:", error);
    }
  }
}

if (!customElements.get('header-component')) {
  customElements.define('header-component', HeaderComponent);
}
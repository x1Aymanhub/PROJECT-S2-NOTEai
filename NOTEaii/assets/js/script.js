// Form validation
document.addEventListener('DOMContentLoaded', function() {
    const loginForm = document.querySelector('.login-form');
    if (loginForm) {
        loginForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const email = this.querySelector('input[name="email"]').value;
            const password = this.querySelector('input[name="password"]').value;
            
            // Basic validation
            if (!email || !password) {
                alert('Please fill in all fields');
                return;
            }
            
            if (!isValidEmail(email)) {
                alert('Please enter a valid email address');
                return;
            }
            
            // If validation passes, submit the form
            this.submit();
        });
    }
});

// Email validation helper function
function isValidEmail(email) {
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return emailRegex.test(email);
}

// Smooth scrolling for navigation links
document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', function(e) {
        e.preventDefault();
        const target = document.querySelector(this.getAttribute('href'));
        if (target) {
            target.scrollIntoView({
                behavior: 'smooth',
                block: 'start'
            });
        }
    });
});

// Add header background on scroll
window.addEventListener('scroll', function() {
    const header = document.querySelector('header');
    if (window.scrollY > 50) {
        header.style.backgroundColor = 'rgba(26, 15, 10, 0.9)';
    } else {
        header.style.backgroundColor = 'transparent';
    }
});

// Remember me functionality
const rememberCheckbox = document.getElementById('remember');
if (rememberCheckbox) {
    rememberCheckbox.addEventListener('change', function() {
        if (this.checked) {
            localStorage.setItem('rememberMe', 'true');
        } else {
            localStorage.removeItem('rememberMe');
        }
    });
    
    // Check if user was remembered
    if (localStorage.getItem('rememberMe') === 'true') {
        rememberCheckbox.checked = true;
    }
}

// Gestion du scroll pour le header
window.addEventListener('scroll', () => {
    const header = document.querySelector('header');
    header.classList.toggle('scrolled', window.scrollY > 0);
});

// Fonction pour afficher un message
function showMessage(text, type = 'success') {
    const messageElement = document.getElementById('message');
    const messageContent = messageElement.querySelector('.message-content');
    messageContent.textContent = text;
    messageElement.className = `message ${type}`;
    
    // Masquer automatiquement le message après 5 secondes
    setTimeout(() => {
        messageElement.className = 'message hidden';
    }, 5000);
}

// Gestionnaire pour le bouton de fermeture
document.addEventListener('DOMContentLoaded', () => {
    const closeButtons = document.querySelectorAll('.close-message');
    closeButtons.forEach(button => {
        button.addEventListener('click', () => {
            const message = button.closest('.message');
            if (message) {
                message.className = 'message hidden';
            }
        });
    });

    // Gestion du formulaire de connexion
    const loginForm = document.querySelector('form[action="php/auth.php"]');
    if (loginForm) {
        loginForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const email = this.querySelector('input[name="email"]').value;
            const password = this.querySelector('input[name="password"]').value;
            
            // Validation des champs
            if (!email || !password) {
                showMessage('Veuillez remplir tous les champs', 'error');
                return;
            }
            
            // Envoi des données via AJAX
            const formData = new FormData(this);
            fetch('php/auth.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showMessage(data.message, 'success');
                    setTimeout(() => {
                        window.location.href = data.redirect;
                    }, 1500);
                } else {
                    showMessage(data.message, 'error');
                }
            })
            .catch(error => {
                showMessage('Une erreur est survenue. Veuillez réessayer.', 'error');
                console.error('Error:', error);
            });
        });
    }

    // Gestion du formulaire d'inscription
    const registerForm = document.getElementById('register-form');
    if (registerForm) {
        registerForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const nom = document.getElementById('nom').value;
            const prenom = document.getElementById('prenom').value;
            const email = document.getElementById('email').value;
            const password = document.getElementById('password').value;
            const confirmPassword = document.getElementById('confirm_password').value;
           
            
            // Validation des champs requis
            if (!nom || !prenom  || !email || !password || !confirmPassword) {
                showMessage('Veuillez remplir tous les champs', 'error');
                return;
            }
            
           
            
            // Validation de l'email
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(email)) {
                showMessage('Veuillez entrer une adresse email valide', 'error');
                return;
            }
            
            // Validation du mot de passe
            if (password.length < 8) {
                showMessage('Le mot de passe doit contenir au moins 8 caractères', 'error');
                return;
            }
            
            // Vérification de la correspondance des mots de passe
            if (password !== confirmPassword) {
                showMessage('Les mots de passe ne correspondent pas', 'error');
                return;
            }
            
            // Envoi des données via AJAX
            const formData = new FormData(this);
            fetch('php/register.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showMessage(data.message, 'success');
                    setTimeout(() => {
                        window.location.href = data.redirect;
                    }, 1500);
                } else {
                    showMessage(data.message, 'error');
                }
            })
            .catch(error => {
                showMessage('Une erreur est survenue. Veuillez réessayer.', 'error');
                console.error('Error:', error);
            });
        });
    }
});

// Animation des labels des champs de formulaire
document.querySelectorAll('.input-box input, .input-box select').forEach(input => {
    input.addEventListener('focus', function() {
        this.parentElement.classList.add('focused');
    });
    
    input.addEventListener('blur', function() {
        if (this.value === '') {
            this.parentElement.classList.remove('focused');
        }
    });
    
    // Pour garder le label en haut si le champ a une valeur
    if (input.value !== '') {
        input.parentElement.classList.add('focused');
    }
});

// Animation de transition entre login et register
document.querySelectorAll('.register-link a, .login-link').forEach(link => {
    link.addEventListener('click', function(e) {
        if (!this.getAttribute('href').includes('#')) {
            e.preventDefault();
            const currentForm = this.closest('.wrapper-login');
            currentForm.style.animation = 'slideOutRight 0.5s ease-out forwards';
            
            setTimeout(() => {
                window.location.href = this.getAttribute('href');
            }, 500);
        }
    });
});

// Animation d'entrée du formulaire
document.addEventListener('DOMContentLoaded', () => {
    const form = document.querySelector('.wrapper-login');
    if (form) {
        form.style.animation = 'slideInRight 0.5s ease-out forwards';
    }
});

// Gestion du menu responsive
const navLinks = document.querySelectorAll('.nav a');
navLinks.forEach(link => {
    link.addEventListener('click', function() {
        navLinks.forEach(l => l.classList.remove('active'));
        this.classList.add('active');
    });
});

// Gestion du formulaire de connexion
document.getElementById('login-form').addEventListener('submit', async (e) => {
    e.preventDefault();
    
    const email = document.getElementById('email').value;
    const password = document.getElementById('password').value;
    
    try {
        const response = await fetch('/php/auth.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ email, password }),
        });
        
        const data = await response.json();
        
        if (data.success) {
            showMessage('Connexion réussie ! Redirection...', 'success');
            setTimeout(() => {
                window.location.href = '/dashboard.php';
            }, 1500);
        } else {
            showMessage(data.message || 'Erreur de connexion. Veuillez réessayer.', 'error');
        }
    } catch (error) {
        showMessage('Une erreur est survenue. Veuillez réessayer plus tard.', 'error');
    }
});

// Event listener for close button
document.addEventListener('DOMContentLoaded', () => {
    const closeButton = document.querySelector('.close-message');
    if (closeButton) {
        closeButton.addEventListener('click', () => {
            const message = closeButton.closest('.message');
            if (message) {
                message.className = 'message hidden';
            }
        });
    }
});

// Example usage:
// showMessage('Login successful!', 'success');
// showMessage('Invalid credentials', 'error');

document.addEventListener('DOMContentLoaded', () => {
    const loginForm = document.querySelector('.login-form');
    if (loginForm) {
        loginForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            
            const email = loginForm.querySelector('input[type="email"]').value;
            const password = loginForm.querySelector('input[type="password"]').value;
            
            try {
                const response = await fetch('/php/auth.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({ email, password }),
                });

                const data = await response.json();
                
                if (data.success) {
                    showMessage('Login successful!', 'success');
                    // Redirect after successful login
                    setTimeout(() => {
                        window.location.href = '/dashboard.php';
                    }, 1000);
                } else {
                    showMessage(data.message || 'Login failed', 'error');
                }
            } catch (error) {
                showMessage('An error occurred. Please try again.', 'error');
            }
        });
    }
});

document.addEventListener('DOMContentLoaded', function() {
    const registerForm = document.getElementById('register-form');
    const submitBtn = document.getElementById('submit-btn');

    if (registerForm && submitBtn) {
        submitBtn.addEventListener('click', function(e) {
            e.preventDefault();
            
            // Récupération des valeurs
            const formData = new FormData(registerForm);
            
            // Debug - afficher les données
            console.log('Envoi des données...', Object.fromEntries(formData));

            fetch('php/register.php', {
                method: 'POST',
                body: formData
            })
            .then(response => {
                console.log('Réponse reçue:', response);
                return response.text().then(text => {
                    try {
                        return JSON.parse(text);
                    } catch (e) {
                        console.error('Erreur parsing JSON:', text);
                        throw new Error('Réponse invalide du serveur');
                    }
                });
            })
            .then(data => {
                if (data.success) {
                    showMessage('Inscription réussie!', 'success');
                    setTimeout(() => {
                        window.location.href = 'index.html';
                    }, 1500);
                } else {
                    showMessage(data.message || 'Erreur lors de l\'inscription', 'error');
                }
            })
            .catch(error => {
                console.error('Erreur:', error);
                showMessage('Une erreur est survenue lors de l\'inscription', 'error');
            });
        });
    }
});

// Custom cursor functionality
document.addEventListener('DOMContentLoaded', () => {
    // Create cursor elements
    const cursorDot = document.createElement('div');
    const cursorOutline = document.createElement('div');
    
    cursorDot.classList.add('cursor-dot');
    cursorOutline.classList.add('cursor-outline');
    
    document.body.appendChild(cursorDot);
    document.body.appendChild(cursorOutline);
    
    let mouseX = 0;
    let mouseY = 0;
    let dotX = 0;
    let dotY = 0;
    let outlineX = 0;
    let outlineY = 0;
    
    // Mouse move event with smooth animation
    document.addEventListener('mousemove', (e) => {
        mouseX = e.clientX;
        mouseY = e.clientY;
    });
    
    // Animation loop for smooth cursor movement
    function animateCursor() {
        // Smooth dot movement
        const dotDx = mouseX - dotX;
        const dotDy = mouseY - dotY;
        dotX += dotDx * 0.35;
        dotY += dotDy * 0.35;
        
        // Smooth outline movement with delay
        const outlineDx = mouseX - outlineX;
        const outlineDy = mouseY - outlineY;
        outlineX += outlineDx * 0.12;
        outlineY += outlineDy * 0.12;
        
        // Update positions
        cursorDot.style.transform = `translate(${dotX}px, ${dotY}px)`;
        cursorOutline.style.transform = `translate(${outlineX}px, ${outlineY}px)`;
        
        requestAnimationFrame(animateCursor);
    }
    
    animateCursor();
    
    // Mouse down event
    document.addEventListener('mousedown', () => {
        cursorDot.classList.add('active');
        cursorOutline.classList.add('active');
    });
    
    // Mouse up event
    document.addEventListener('mouseup', () => {
        cursorDot.classList.remove('active');
        cursorOutline.classList.remove('active');
    });
    
    // Mouse leave event
    document.addEventListener('mouseleave', () => {
        cursorDot.style.display = 'none';
        cursorOutline.style.display = 'none';
    });
    
    // Mouse enter event
    document.addEventListener('mouseenter', () => {
        cursorDot.style.display = 'block';
        cursorOutline.style.display = 'block';
    });
    
    // Add hover effect for interactive elements
    const interactiveElements = document.querySelectorAll('a, button, input, select, .btn');
    interactiveElements.forEach(element => {
        element.addEventListener('mouseenter', () => {
            cursorDot.classList.add('hover');
            cursorOutline.classList.add('hover');
        });
        
        element.addEventListener('mouseleave', () => {
            cursorDot.classList.remove('hover');
            cursorOutline.classList.remove('hover');
        });
    });

    // Magnetic effect for buttons
    const magneticButtons = document.querySelectorAll('.btn');
    magneticButtons.forEach(button => {
        button.classList.add('magnetic');
        
        button.addEventListener('mousemove', (e) => {
            const rect = button.getBoundingClientRect();
            const x = e.clientX - rect.left - rect.width / 2;
            const y = e.clientY - rect.top - rect.height / 2;
            
            button.style.transform = `translate(${x * 0.2}px, ${y * 0.2}px)`;
        });
        
        button.addEventListener('mouseleave', () => {
            button.style.transform = 'translate(0px, 0px)';
        });
    });

    // Add clickable class to interactive elements
    document.querySelectorAll('a, button, .btn, input[type="submit"]').forEach(element => {
        element.classList.add('clickable');
    });

    // Text selection effect
    document.addEventListener('selectionchange', () => {
        const selection = window.getSelection();
        if (selection.toString().length > 0) {
            cursorOutline.style.transform = `translate(${outlineX}px, ${outlineY}px) scale(1.5)`;
        } else {
            cursorOutline.style.transform = `translate(${outlineX}px, ${outlineY}px)`;
        }
    });
});
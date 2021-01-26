// Habilita botão de entrar
function loginFieldChecker() {
    const formLogin = document.querySelector('#form-login');
    const entryLogin = formLogin.querySelector('input[name="entry"]');
    const passwordLogin = formLogin.querySelector('input[name="password"]');
    const buttonLogin = formLogin.querySelector('section > button')

    entryLogin.addEventListener('input', changeButton)
    passwordLogin.addEventListener('input', () => {
        changeButton()
    })

    function changeButton() {
        const statusButton = {
            entry: false,
            password: false
        }

        statusButton.entry = entryLogin.value.trim().length > 0 ? true : false
        statusButton.password = passwordLogin.value.trim().length >= 6 ? true : false

        if (statusButton.entry && statusButton.password) {
            buttonLogin.removeAttribute('disabled')
        } else {
            buttonLogin.setAttribute('disabled', '')
        }
    }
}
if (document.querySelector('#form-login'))
    loginFieldChecker()

// Habilita botão de cadastrar
function registerFieldChecker() {
    const formRegister = document.querySelector('#form-register');
    const usernameRegister = formRegister.querySelector('input[name="username"]');
    const speciesRegister = formRegister.querySelector('select');
    const emailRegister = formRegister.querySelector('input[name="email"]');
    const passwordRegister = formRegister.querySelector('input[name="password"]');
    const buttonRegister = formRegister.querySelector('section > button')

    usernameRegister.addEventListener('input', changeButton)
    speciesRegister.addEventListener('input', changeButton)
    emailRegister.addEventListener('input', changeButton)
    passwordRegister.addEventListener('input', changeButton)

    function changeButton() {
        const statusButton = {
            username: false,
            species: false,
            email: false,
            password: false
        }

        statusButton.username = usernameRegister.value.trim().length > 0 ? true : false
        statusButton.species = Number(speciesRegister.value) > 0 ? true : false
        statusButton.email = emailRegister.value.trim().length > 0 ? true : false
        statusButton.password = passwordRegister.value.trim().length >= 6 ? true : false

        if (statusButton.username && statusButton.species && statusButton.email && statusButton.password) {
            buttonRegister.removeAttribute('disabled')
        } else {
            buttonRegister.setAttribute('disabled', '')
        }
    }
}
if (document.querySelector('#form-register'))
    registerFieldChecker()

// Troca a opção de mostrar ou não a senha
if (document.querySelector('.showPass')) {
    const buttonPassword = document.querySelector('.showPass')
    buttonPassword.addEventListener('click', showPassButton)

    function showPassButton() {
        const passwordInput = document.querySelector('input[name="password"]');
        buttonPassword.classList.toggle('show')

        if (buttonPassword.classList.contains('show')) {
            passwordInput.setAttribute('type', 'text')
            buttonPassword.innerHTML = '<i class="fi-rr-eye"></i>'
        } else {
            passwordInput.setAttribute('type', 'password')
            buttonPassword.innerHTML = '<i class="fi-rr-eye-crossed"></i>'
        }
    }
}

// Show/Hide menu
if (document.querySelector('.profile-picture')) {
    const subMenuButton = document.querySelector('.profile-picture')
    subMenuButton.addEventListener('click', e => {
        e.target.classList.toggle('sub-menu-active')
    })

    document.addEventListener('click', e => {
        const el = e.target

        if (!el.classList.contains('sub-menu-active')) {
            subMenuButton.classList.remove('.sub-menu-active')
        }
    })
}

if (document.querySelector('main#photo')) {
    const url = new URL(window.location.href)
    const photoname = url.searchParams.get("p")

    const infoDelete = {
        title: 'Deseja mesmo excluir essa foto?',
        options: [
            {
                element: 'a',
                text: 'Excluir foto',
                attributes: [
                    {
                        name: 'href',
                        value: `/delete-photography?p=${photoname}`
                    }
                ]
            }
        ]
    }

    const btnDeletePhoto = document.querySelectorAll('.user .deletePhoto')
    btnDeletePhoto.forEach(btn => {
        btn.addEventListener('click', (e) => {
            showModal(infoDelete)
        })
    });
}

if (document.querySelector('main#edit')) {
    document.querySelector('input#change-picture').addEventListener('change', () => {
        document.querySelector('#formChangePicture').submit()
    })

    const infoDisable = {
        title: 'Desativar conta',
        options: [
            {
                element: 'a',
                text: 'Confirmar',
                attributes: [
                    {
                        name: 'href',
                        value: '/disable-account'
                    }
                ]
            }
        ]
    }

    const infoPicture = {
        title: 'Alterar foto do perfil',
        options: [
            {
                element: 'label',
                text: 'Carregar foto',
                attributes: [
                    {
                        name: 'for',
                        value: 'change-picture'
                    }
                ],
                color: '#0095f6'
            },
            {
                element: 'a',
                text: 'Remover foto atual',
                attributes: [
                    {
                        name: 'href',
                        value: 'remove-profile-picture'
                    }
                ]
            }
        ]
    }

    const btnDisable = document.querySelector('main#edit .btnDisable')
    btnDisable.addEventListener('click', () => {
        showModal(infoDisable)
    })

    const labelPicture = document.querySelector('main#edit .info-picture > label')
    labelPicture.addEventListener('click', (e) => {
        showModal(infoPicture)
    })

    const iconPicture = document.querySelector('main#edit .profile-picture')
    iconPicture.addEventListener('click', (e) => {
        showModal(infoPicture)
    })
}

if (document.querySelector('main#followers')) {
    const infoUnfollow = {
        title: 'Deixar de seguir',
        options: [
            {
                element: 'a',
                text: 'Deixar de seguir',
                attributes: [
                    {
                        name: 'href',
                        value: '/unfollow'
                    }
                ]
            }
        ]
    }

    const btnUnfollow = document.querySelectorAll('main#followers .unfollow-btn')
    btnUnfollow.forEach(btn => {
        btn.addEventListener('click', (e) => {
            showModal(infoUnfollow, e.target)
        })
    });
}

if (document.querySelector('main#following')) {
    const infoUnfollow = {
        title: 'Deixar de seguir',
        options: [
            {
                element: 'a',
                text: 'Deixar de seguir',
                attributes: [
                    {
                        name: 'href',
                        value: '/unfollow'
                    }
                ]
            }
        ]
    }

    const btnUnfollow = document.querySelectorAll('main#following .unfollow-btn')
    btnUnfollow.forEach(btn => {
        btn.addEventListener('click', (e) => {
            showModal(infoUnfollow, e.target)
        })
    });
}

if (document.querySelector('main#profile')) {
    const myPhotos = document.querySelector('.photos')
    const mySaved = document.querySelector('.saved')

    const btnMyPhotos = document.querySelector('.options .btnMyPhotos i')
    const btnSaved = document.querySelector('.options .btnSaved i')

    if (mySaved) {
        changeCategory()

        window.addEventListener('hashchange', changeCategory)

        function changeCategory() {
            if (location.hash === '#saved') {
                myPhotos.classList.add('hidden')
                btnMyPhotos.parentElement.classList.remove('show')
                btnMyPhotos.className = 'fi-rr-grid'

                mySaved.classList.remove('hidden')
                btnSaved.parentElement.classList.add('show')
                btnSaved.className = 'fi-sr-bookmark'
            } else {
                myPhotos.classList.remove('hidden')
                btnMyPhotos.parentElement.classList.add('show')
                btnMyPhotos.className = 'fi-sr-grid'

                mySaved.classList.add('hidden')
                btnSaved.parentElement.classList.remove('show')
                btnSaved.className = 'fi-rr-bookmark'
            }
        }
    } else {
        myPhotos.classList.remove('hidden')
        btnMyPhotos.parentElement.classList.add('show')
        btnMyPhotos.className = 'fi-sr-grid'
    }
}

// Abre o modal
function showModal(info, user) {
    const modalContainer = document.querySelector('.modal-container')
    const title = modalContainer.querySelector('.title')
    const options = modalContainer.querySelector('.options')
    options.innerHTML = ''

    title.textContent = user ? `${info.title}\n@${user.getAttribute('data-user')}?` : info.title
    info.options.forEach(o => {
        const el = document.createElement(o.element)
        el.textContent = o.text

        o.attributes.forEach(attr => {
            el.setAttribute(attr.name, user ? `${attr.value}?user=${user.getAttribute('data-user')}` : attr.value)
        });

        el.style.color = o.color
        options.appendChild(el)
    });

    modalContainer.classList.add('show')
}

// Hide modal
if (document.querySelector('.modal-container')) {
    const modalContainer = document.querySelector('.modal-container')

    document.addEventListener('click', e => {
        if (e.target.classList.contains('modal-container') || e.target.classList.contains('cancelBtn')) {
            modalContainer.classList.remove('show')
        }
    })
}

if (document.querySelector('main#home') || document.querySelector('main#profile')) {
    const formPhoto = document.querySelector('#formPostPhotography')

    document.querySelector('#add-photo-input').addEventListener('change', () => {
        formPhoto.submit()
    })
}
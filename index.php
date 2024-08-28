<?php
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("Заявка в amoCRM"); ?>

<style>
    .form-group {
        margin-bottom: 1rem;
    }
    label {
        display: block;
        margin-bottom: 0.5rem;
        color: #555;
    }
    input[type="text"],
    input[type="email"],
    input[type="tel"],
    input[type="number"] {
        width: 100%;
        padding: 0.75rem;
        border: 1px solid #ddd;
        border-radius: 4px;
        box-sizing: border-box;
    }
    input[type="text"]:focus,
    input[type="email"]:focus,
    input[type="tel"]:focus,
    input[type="number"]:focus {
        border-color: #007bff;
        outline: none;
    }
    button {
        width: 100%;
        padding: 0.75rem;
        border: none;
        border-radius: 4px;
        background-color: #007bff;
        color: #fff;
        font-size: 1rem;
        cursor: pointer;
        transition: background-color 0.3s;
    }
    button:hover {
        background-color: #0056b3;
    }
    .error-message {
        color: #dc3545;
        font-size: 0.875rem;
        margin-top: 0.5rem;
    }

    /* Modal Styles */
    .modal {
        display: none; /* Hidden by default */
        position: fixed; /* Stay in place */
        z-index: 1; /* Sit on top */
        left: 0;
        top: 0;
        width: 100%; /* Full width */
        height: 100%; /* Full height */
        overflow: auto; /* Enable scroll if needed */
        background-color: rgba(0,0,0,0.4); /* Black w/ opacity */
    }
    .modal-content {
        background-color: #fefefe;
        margin: 15% auto; /* 15% from the top and centered */
        padding: 20px;
        border: 1px solid #888;
        width: 80%; /* Could be more or less, depending on screen size */
        max-width: 500px;
        position: relative;
        border-radius: 4px;
    }
    .close {
        color: #aaa;
        float: right;
        font-size: 28px;
        font-weight: bold;
    }
    .close:hover,
    .close:focus {
        color: black;
        text-decoration: none;
        cursor: pointer;
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.getElementById('lead-form');

        // Применяем маску к полю телефона
        maskPhone('#phone');

        form.addEventListener('submit', function(event) {
            event.preventDefault();

            const name = document.getElementById('name').value;
            const email = document.getElementById('email').value;
            const phone = document.getElementById('phone').value;
            const price = document.getElementById('price').value;

            let hasErrors = false;

            // Clear previous errors
            document.querySelectorAll('.error-message').forEach(el => el.textContent = '');

            if (!/^[а-яА-ЯёЁ\s]+$/.test(name)) {
                document.getElementById('name-error').textContent = 'Имя должно содержать только буквы и пробелы.';
                hasErrors = true;
            }

            if (!/^\S+@\S+\.\S+$/.test(email)) {
                document.getElementById('email-error').textContent = 'Введите корректный email.';
                hasErrors = true;
            }

            // Phone number validation pattern
            const phonePattern = /^\+7 \(\d{3}\) \d{3}-\d{2}-\d{2}$/;

            // Phone number check
            if (!phonePattern.test(phone)) {
                document.getElementById('phone-error').textContent = 'Телефон должен быть в формате +7 (XXX) XXX-XX-XX.';
                hasErrors = true;
            }

            if (isNaN(price) || price <= 0) {
                document.getElementById('price-error').textContent = 'Цена должна быть положительным числом.';
                hasErrors = true;
            }

            if (hasErrors) {
                return;
            }

            const formData = new FormData(form);
            fetch('submit.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(result => {
                if (result.errors) {
                    // Show error message from the server
                    showModal('Ошибка: ' + result.errors);
                } else {
                    console.log('Success:', result);
                    showModal('Заявка отправлена!');
                }
            })
            .catch(errors => {
                console.error('Error:', errors);
                showModal('Произошла ошибка: ' + errors.message);
            });
        });

        function showModal(message) {
            const modal = document.getElementById('myModal');
            const modalMessage = document.getElementById('modal-message');
            const span = document.getElementsByClassName('close')[0];

            modal.style.display = 'block';
            modalMessage.textContent = message;

            span.onclick = function() {
                modal.style.display = 'none';
            }

            window.onclick = function(event) {
                if (event.target == modal) {
                    modal.style.display = 'none';
                }
            }
        }

        function maskPhone(selector, masked = '+7 (___) ___-__-__') {
            const elems = document.querySelectorAll(selector);

            function mask(event) {
                const keyCode = event.keyCode;
                const template = masked,
                    def = template.replace(/\D/g, ""),
                    val = this.value.replace(/\D/g, "");
                let i = 0,
                    newValue = template.replace(/[_\d]/g, function (a) {
                        return i < val.length ? val.charAt(i++) || def.charAt(i) : a;
                    });
                i = newValue.indexOf("_");
                if (i !== -1) {
                    newValue = newValue.slice(0, i);
                }
                let reg = template.substr(0, this.value.length).replace(/_+/g,
                    function (a) {
                        return "\\d{1," + a.length + "}";
                    }).replace(/[+()]/g, "\\{{input}}");
                reg = new RegExp("^" + reg + "$");
                if (!reg.test(this.value) || this.value.length < 5 || keyCode > 47 && keyCode < 58) {
                    this.value = newValue;
                }
                if (event.type === "blur" && this.value.length < 5) {
                    this.value = "";
                }

            }

            for (const elem of elems) {
                elem.addEventListener("input", mask);
                elem.addEventListener("focus", mask);
                elem.addEventListener("blur", mask);
            }
            
        }
    });
</script>


<div class="container">
    <h1>Форма заявки</h1>
    <form id="lead-form">
        <div class="form-group">
            <label for="name">Имя:</label>
            <input type="text" id="name" name="name" required>
            <div class="error-message" id="name-error"></div>
        </div>

        <div class="form-group">
            <label for="email">Email:</label>
            <input type="email" id="email" name="email" required>
            <div class="error-message" id="email-error"></div>
        </div>

        <div class="form-group">
            <label for="phone">Телефон:</label>
            <input type="tel" autocomplete="tel" id="phone" name="phone" required>
            <div class="error-message" id="phone-error"></div>
        </div>

        <div class="form-group">
            <label for="price">Цена:</label>
            <input type="number" id="price" name="price" required min="0.01" step="0.01">
            <div class="error-message" id="price-error"></div>
        </div>

        <button type="submit">Отправить</button>
    </form>
</div>

<!-- Modal HTML -->
<div id="myModal" class="modal">
    <div class="modal-content">
        <span class="close">&times;</span>
        <p id="modal-message"></p>
        <button onclick="document.getElementById('myModal').style.display='none'">Ок</button>
    </div>
</div>

<?php require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php"); ?>

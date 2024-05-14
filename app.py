from flask import Flask, request, jsonify
import requests
import json
import os

app = Flask(__name__)

# Папка для сохранения JSON файлов
os.makedirs('users', exist_ok=True)

@app.route('/webhook', methods=['POST'])
def webhook():
    data = request.json
    message = data.get('messages', [{}])[0].get('text', '').strip()
    phone = data.get('messages', [{}])[0].get('from', '')

    if message.lower() != "":
        # Отправка приветственного сообщения
        response_message = "Приветствую. Представьтесь пожалуйста (ваше ФИО)"
        send_message(phone, response_message)
    else:
        # Сохранение ФИО пользователя в файл
        user_data = {'phone': phone, 'name': message}
        with open(f'users/{phone}.json', 'w') as f:
            json.dump(user_data, f)

    return jsonify(success=True), 200

def send_message(phone, text):
    # Здесь должен быть код для отправки сообщения через Wazzup
    # Используйте ваш API ключ и конечную точку API
    url = "https://api.wazzup24.com/v3/channels/sendMessage"
    headers = {'Authorization': 'Bearer b0f9052056924fe59375a6c76b052889'}
    data = {
        'phone': phone,
        'text': text
    }
    response = requests.post(url, headers=headers, json=data)
    return response.json()

if __name__ == '__main__':
    app.run(debug=True, port=5000)
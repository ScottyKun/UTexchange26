
from flask import Flask, request, jsonify
from flask_cors import CORS
import torch
import json
import random

from app.models.neural_net import NeuralNet
from app.utils.nlp_utils import bag_of_words, tokenize

app = Flask(__name__)
CORS(app)  # autorise les requêtes depuis ton site PHP

device = torch.device('cuda' if torch.cuda.is_available() else 'cpu')

# Charger le modèle
FILE = "chatbot_model.pth"
data = torch.load(FILE, map_location=device)

input_size = data["input_size"]
hidden_size = data["hidden_size"]
output_size = data["output_size"]
all_words = data["all_words"]
tags = data["tags"]
model_state = data["model_state"]

model = NeuralNet(input_size, hidden_size, output_size).to(device)
model.load_state_dict(model_state)
model.eval()

# Charger intents
with open('data/intents/intents.json', 'r', encoding='utf-8') as f:
    intents = json.load(f)


@app.route('/')
def home():
    return jsonify({"message": "Chatbot API is running!"})


@app.route('/chat', methods=['POST'])
def chat():
    try:
        # 🔥 Accepte JSON ET form-data (très important)
        if request.is_json:
            data_json = request.get_json()
            user_message = data_json.get("message") if data_json else None
        else:
            user_message = request.form.get("message")

        # 🔒 Vérification robuste
        if not user_message or str(user_message).strip() == "":
            return jsonify({"response": "Je n'ai pas bien entendu, pouvez-vous répéter ?"}), 200

        # 🧠 NLP
        sentence = tokenize(user_message)
        X = bag_of_words(sentence, all_words)
        X = torch.from_numpy(X).float().unsqueeze(0).to(device)

        output = model(X)
        _, predicted = torch.max(output, dim=1)
        tag = tags[predicted.item()]

        probs = torch.softmax(output, dim=1)
        prob = probs[0][predicted.item()]

        # 🎯 Réponse intelligente
        if prob.item() > 0.6:
            for intent in intents['intents']:
                if tag == intent["tag"]:
                    response = random.choice(intent['responses'])
                    return jsonify({"response": response})

        return jsonify({"response": "Je ne comprends pas 😅"})

    except Exception as e:
        print(f"Erreur serveur : {e}")
        return jsonify({"response": "Oups, mon cerveau a eu un bug technique 😵"}), 500


if __name__ == '__main__':
    app.run(host="0.0.0.0", port=5000, debug=True)

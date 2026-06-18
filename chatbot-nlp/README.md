# API Chatbot NLP

Interface de traitement du langage naturel utilisant PyTorch et Flask.

## Structure
- `/app` : Contient le point d'entrée `main.py`.
- `chatbot_model.pth` : Modèle entraîné (réseau de neurones).
- `requirements.txt` : Liste des dépendances Python.
## Environnement
```bash 
#Preciser la version de Python (ex: python3.8) si nécessaire
cd chatbot-nlp
python -m venv venv
source venv/bin/activate  # Sur Windows : .\venv\Scripts\activate.ps1

```
## Installation
```bash
pip install -r requirements.txt
python -c "import nltk; nltk.download('punkt_tab'); nltk.download('punkt'); nltk.download('stopwords')" #si nécessaire pour les ressources NLTK
python train.py # Entraîne le modèle et génère chatbot_model.pth
```
`.env` : Créer un fichier `.env` pour les variables d'environnement dans le dossier `chatbot-nlp` avec pour contenu `.env.example` adapté à votre environnement.
## Test
Pour voir le chatbot en action (mode terminal), exécutez le script de test :
```bash
 python test_chat.py
```
## Lancement de l'API du chatbot
```bash
python -m app.main
```
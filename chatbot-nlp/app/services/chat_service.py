import torch
import random
import json
import sys
import os

# Ajouter le dossier racine au path Python
sys.path.append(os.path.dirname(os.path.dirname(os.path.dirname(__file__))))

from app.models.neural_net import NeuralNet
from app.utils.nlp_utils import tokenize, bag_of_words


class ChatService:
    """
    Service pour charger le modèle et prédire les réponses
    """
    
    def __init__(self, model_path='chatbot_model.pth', intents_path='data/intents/intents.json'):
        """
        Initialise le service en chargeant le modèle et les intents
        
        Args:
            model_path: Chemin vers le modèle entraîné (.pth)
            intents_path: Chemin vers le fichier intents.json
        """
        self.device = torch.device('cuda' if torch.cuda.is_available() else 'cpu')
        
        # Charger le modèle
        print(f"📦 Chargement du modèle depuis {model_path}...")
        data = torch.load(model_path, map_location=self.device)
        
        self.input_size = data["input_size"]
        self.hidden_size = data["hidden_size"]
        self.output_size = data["output_size"]
        self.all_words = data["all_words"]
        self.tags = data["tags"]
        
        # Créer et charger le modèle
        self.model = NeuralNet(self.input_size, self.hidden_size, self.output_size).to(self.device)
        self.model.load_state_dict(data["model_state"])
        self.model.eval()  # Mode évaluation (désactive dropout, etc.)
        
        print(f"✅ Modèle chargé avec succès!")
        print(f"   - Vocabulaire: {len(self.all_words)} mots")
        print(f"   - Intents: {len(self.tags)} catégories")
        
        # Charger les intents (pour les réponses)
        with open(intents_path, 'r', encoding='utf-8') as f:
            self.intents = json.load(f)
        
        print(f"✅ Intents chargés depuis {intents_path}")
    
    
    def get_response(self, message):
        """
        Génère une réponse pour un message donné
        
        Args:
            message: Message de l'utilisateur (string)
        
        Returns:
            dict: {
                'response': réponse du bot,
                'intent': intent détecté,
                'confidence': score de confiance (0-1)
            }
        """
        # Tokenize et créer le bag of words
        tokenized = tokenize(message)
        X = bag_of_words(tokenized, self.all_words)
        X = X.reshape(1, X.shape[0])  # Ajouter une dimension batch
        X = torch.from_numpy(X).to(self.device)
        
        # Prédiction
        with torch.no_grad():  # Pas besoin de gradients pour la prédiction
            output = self.model(X)
            _, predicted_idx = torch.max(output, dim=1)
            
            # Calculer les probabilités avec softmax
            probs = torch.softmax(output, dim=1)
            confidence = probs[0][predicted_idx.item()].item()
        
        # Récupérer le tag prédit
        predicted_tag = self.tags[predicted_idx.item()]
        
        # Si la confiance est trop faible, utiliser l'intent "default"
        if confidence < 0.75:
            predicted_tag = "default"
        
        # Trouver les réponses correspondantes
        for intent in self.intents['intents']:
            if intent['tag'] == predicted_tag:
                response = random.choice(intent['responses'])
                return {
                    'response': response,
                    'intent': predicted_tag,
                    'confidence': round(confidence, 4)
                }
        
        # Fallback si aucun intent trouvé
        return {
            'response': "Je ne suis pas sûr de comprendre. Peux-tu reformuler ?",
            'intent': 'unknown',
            'confidence': 0.0
        }


# Test du service (optionnel)
if __name__ == "__main__":
    # Créer le service
    service = ChatService()
    
    # Tests
    test_messages = [
        "Bonjour",
        "Comment tu t'appelles ?",
        "Raconte une blague",
        "Au revoir",
        "Merci beaucoup",
        "C'est quoi UTexchange ?"
    ]
    
    print("\n🧪 Tests du chatbot:\n")
    for msg in test_messages:
        result = service.get_response(msg)
        print(f"👤 User: {msg}")
        print(f"🤖 Bot: {result['response']}")
        print(f"   Intent: {result['intent']} (confiance: {result['confidence']})")
        print()
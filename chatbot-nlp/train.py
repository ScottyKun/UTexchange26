import json
import numpy as np
import torch
import torch.nn as nn
from torch.utils.data import Dataset, DataLoader
from app.utils.nlp_utils import tokenize, stem, bag_of_words
from app.models.neural_net import NeuralNet

# Charger les intents
with open('data/intents/intents.json', 'r', encoding='utf-8') as f:
    intents = json.load(f)

# Listes pour stocker les données
all_words = []  # Tous les mots uniques
tags = []       # Tous les tags (intents)
xy = []         # Paires (pattern, tag)

# Parser les intents
print("📚 Chargement des données...")
for intent in intents['intents']:
    tag = intent['tag']
    tags.append(tag)
    
    for pattern in intent['patterns']:
        # Tokenizer chaque pattern
        words = tokenize(pattern)
        all_words.extend(words)
        xy.append((words, tag))

print(f" {len(xy)} patterns chargés")

print(f" {len(tags)} tags trouvés: {tags}")

# Preprocessing
print("\n🔧 Preprocessing...")

ignore_words = ['?', '!', '.', ',', '/', '\\', '(', ')', '[', ']', '{', '}', ':', ';', '"', "'", '-', '_']

# Stemming + nettoyage
all_words = [stem(w) for w in all_words if w not in ignore_words]

# Supprimer doublons
all_words = sorted(set(all_words))
tags = sorted(set(tags))

print(f" {len(all_words)} mots uniques après stemming")

# Création des données d'entraînement
X_train = []
y_train = []

for (pattern_sentence, tag) in xy:
    bag = bag_of_words(pattern_sentence, all_words)
    X_train.append(bag)

    label = tags.index(tag)
    y_train.append(label)

X_train = np.array(X_train)
y_train = np.array(y_train)

print(f" X_train shape: {X_train.shape}")
print(f" y_train shape: {y_train.shape}")

class ChatDataset(Dataset):
    def __init__(self):
        self.n_samples = len(X_train)
        self.x_data = X_train
        self.y_data = y_train

    def __getitem__(self, index):
        return self.x_data[index], self.y_data[index]

    def __len__(self):
        return self.n_samples
    

batch_size = 8
hidden_size = 8
output_size = len(tags)
input_size = len(all_words)
learning_rate = 0.001
num_epochs = 300  # réduit (suffisant)

dataset = ChatDataset()

train_loader = DataLoader(
    dataset=dataset,
    batch_size=batch_size,
    shuffle=True,
    num_workers=0
)

device = torch.device('cuda' if torch.cuda.is_available() else 'cpu')

model = NeuralNet(input_size, hidden_size, output_size).to(device)

criterion = nn.CrossEntropyLoss()
optimizer = torch.optim.Adam(model.parameters(), lr=learning_rate)

print("\n🚀 Entraînement...\n")

for epoch in range(num_epochs):
    for (words, labels) in train_loader:
        words = words.to(device)
        labels = labels.to(dtype=torch.long).to(device)

        # Forward
        outputs = model(words)
        loss = criterion(outputs, labels)

        # Backward
        optimizer.zero_grad()
        loss.backward()
        optimizer.step()

    if (epoch+1) % 50 == 0:
        print(f'Epoch [{epoch+1}/{num_epochs}], Loss: {loss.item():.4f}')

print("\n✅ Entraînement terminé")

data = {
    "model_state": model.state_dict(),
    "input_size": input_size,
    "hidden_size": hidden_size,
    "output_size": output_size,
    "all_words": all_words,
    "tags": tags
}

FILE = "chatbot_model.pth"
torch.save(data, FILE)

print(f"💾 Modèle sauvegardé dans {FILE}")

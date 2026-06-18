import torch
import torch.nn as nn

class NeuralNet(nn.Module):
    """
    Réseau de neurones pour la classification d'intents
    
    Architecture:
    - Couche d'entrée (input_size)
    - Couche cachée 1 (hidden_size)
    - Couche cachée 2 (hidden_size)
    - Couche de sortie (num_classes)
    
    Activation: ReLU entre les couches
    """
    
    def __init__(self, input_size, hidden_size, num_classes):
        """
        Args:
            input_size: Taille du bag of words (nombre de mots uniques)
            hidden_size: Nombre de neurones dans les couches cachées
            num_classes: Nombre d'intents (catégories)
        """
        super(NeuralNet, self).__init__()
        
        # Couche 1: input -> hidden
        self.l1 = nn.Linear(input_size, hidden_size)
        
        # Couche 2: hidden -> hidden
        self.l2 = nn.Linear(hidden_size, hidden_size)
        
        # Couche 3: hidden -> output
        self.l3 = nn.Linear(hidden_size, num_classes)
        
        # Fonction d'activation ReLU
        self.relu = nn.ReLU()
    
    
    def forward(self, x):
        """
        Propagation avant (forward pass)
        
        Args:
            x: Tenseur d'entrée (bag of words)
        
        Returns:
            Tenseur de sortie (scores pour chaque classe)
        """
        # x -> l1 -> ReLU -> l2 -> ReLU -> l3 -> output
        out = self.l1(x)
        out = self.relu(out)
        out = self.l2(out)
        out = self.relu(out)
        out = self.l3(out)
        # Pas de softmax ici, il sera appliqué pendant la prédiction
        return out


# Test du modèle (optionnel)
if __name__ == "__main__":
    # Exemple de paramètres
    input_size = 50   # 50 mots dans le vocabulaire
    hidden_size = 8   # 8 neurones cachés
    num_classes = 5   # 5 intents différents
    
    # Créer le modèle
    model = NeuralNet(input_size, hidden_size, num_classes)
    
    # Créer un exemple d'entrée (batch de 1 phrase)
    x = torch.randn(1, input_size)  # 1 phrase, 50 features
    
    # Forward pass
    output = model(x)
    
    print(f"Input shape: {x.shape}")
    print(f"Output shape: {output.shape}")
    print(f"Output: {output}")
    print("\n✅ Modèle créé avec succès!")

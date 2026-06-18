import nltk
from nltk.stem.porter import PorterStemmer
import numpy as np

# Initialiser le stemmer
stemmer = PorterStemmer()

def tokenize(sentence):
    """
    Découpe une phrase en mots (tokens)
    Exemple: "Comment vas-tu ?" -> ["Comment", "vas", "tu", "?"]
    """
    return nltk.word_tokenize(sentence) 


def stem(word):
    """
    Réduit un mot à sa racine (stem)
    Exemples:
    - "aider", "aide", "aidons" -> "aid"
    - "organisateur", "organisation", "organise" -> "organ"
    """
    return stemmer.stem(word.lower())


def bag_of_words(tokenized_sentence, all_words):
    """
    Convertit une phrase en vecteur de 0 et 1 (bag of words)
    
    Exemple:
    sentence = ["bonjour", "comment"]
    all_words = ["hi", "bonjour", "je", "toi", "au revoir", "comment"]
    bag = [0, 1, 0, 0, 0, 1]
    
    Args:
        tokenized_sentence: liste de mots (déjà tokenizés)
        all_words: liste de tous les mots du vocabulaire
    
    Returns:
        numpy array du bag of words
    """
    # Stemmer chaque mot de la phrase
    tokenized_sentence = [stem(word) for word in tokenized_sentence]
    
    # Initialiser le bag avec des 0
    bag = np.zeros(len(all_words), dtype=np.float32)
    
    # Mettre 1 aux positions des mots présents
    for idx, word in enumerate(all_words):
        if word in tokenized_sentence:
            bag[idx] = 1.0
    
    return bag


# Exemple d'utilisation (pour test)
if __name__ == "__main__":
    # Test tokenization
    sentence = "Bonjour, comment vas-tu ?"
    tokens = tokenize(sentence)
    print(f"Tokens: {tokens}")
    
    # Test stemming
    words = ["aider", "aide", "aidons", "organisateur", "organisation"]
    stemmed = [stem(w) for w in words]
    print(f"Stemmed: {stemmed}")
    
    # Test bag of words
    all_words = ["bonjour", "comment", "vas", "tu", "aide"]
    tokenized = ["bonjour", "comment"]
    bag = bag_of_words(tokenized, all_words)
    print(f"Bag of words: {bag}")
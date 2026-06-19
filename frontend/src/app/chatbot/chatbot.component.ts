import { Component, OnInit, Inject } from '@angular/core';
import { ChatbotService } from 'src/services/chatbot.service';

@Component({
  selector: 'app-chatbot',
  templateUrl: './chatbot.component.html',
  styleUrls: ['./chatbot.component.css']
})
export class ChatbotComponent implements OnInit {

  isOpen: boolean = true;
  userMessage: string = '';
  
  // ← AJOUTER : flag pour savoir si le dernier message vient du micro
  private lastInputWasVoice: boolean = false;

  messages: any[] = [
    {
      sender: 'michele',
      text: 'Hello ! C\'est Michele. Comment puis-je t\'aider ?'
    }
  ];

  constructor(@Inject(ChatbotService) private chatbotService: ChatbotService) { }

  ngOnInit(): void { }

  toggleChat(): void {
    this.isOpen = !this.isOpen;
  }

  clearChat(): void {
    this.messages = [
      {
        sender: 'michele',
        text: 'Hello ! C\'est Michele. Comment puis-je t\'aider ?'
      }
    ];
  }

  enoyerMessage(): void {
    if (!this.userMessage.trim()) return;

    const msg = this.userMessage.trim();
    this.messages.push({ sender: 'user', text: msg });
    
    // ← CAPTURER le flag avant de le réinitialiser
    const wasVoice = this.lastInputWasVoice;
    
    this.userMessage = '';
    this.lastInputWasVoice = false; // ← RÉINITIALISER après chaque envoi

    this.chatbotService.sendMessage(msg).subscribe({
      next: (data: { response: string; }) => {
        const responseText = data?.response || 'Je n\'ai pas trouvé de réponse.';
        this.messages.push({ sender: 'michele', text: responseText });
        
        // ← PARLER uniquement si l'entrée était vocale
        if (wasVoice) {
          this.speak(responseText);
        }
      },
      error: (err: any) => {
        console.error(err);
        const errorMessage = 'Le serveur est indisponible.';
        this.messages.push({ sender: 'system', text: errorMessage });
        
        // ← Idem pour les erreurs
        if (wasVoice) {
          this.speak(errorMessage);
        }
      }
    });
  }

  startVoiceRecognition(): void {
    const SpeechRecognition =
      (window as any).SpeechRecognition ||
      (window as any).webkitSpeechRecognition;

    if (!SpeechRecognition) {
      const message = 'La reconnaissance vocale n\'est pas supportée sur ce navigateur.';
      this.messages.push({ sender: 'system', text: message });
      this.speak(message); // ← Ce message système reste vocal, c'est cohérent
      return;
    }

    const recognition = new SpeechRecognition();
    recognition.lang = 'fr-FR';
    recognition.continuous = false;
    recognition.interimResults = false;
    recognition.maxAlternatives = 1;
    recognition.start();

    recognition.onstart = () => {
      this.messages.push({ sender: 'system', text: '🎤 Je vous écoute...' });
    };

    recognition.onresult = (event: any) => {
      this.userMessage = event.results[0][0].transcript;
      this.lastInputWasVoice = true; // ← MARQUER que l'entrée est vocale
      this.enoyerMessage();
    };

    recognition.onerror = (event: any) => {
      console.error('Erreur reconnaissance vocale :', event.error);
      let message = 'Erreur lors de la reconnaissance vocale.';
      if (event.error === 'not-allowed') {
        message = 'Veuillez autoriser l\'accès au microphone.';
      }
      this.messages.push({ sender: 'system', text: message });
    };
  }

  speak(text: string): void {
    if (!('speechSynthesis' in window)) return;
    speechSynthesis.cancel();
    const utterance = new SpeechSynthesisUtterance(text);
    utterance.lang = 'fr-FR';
    utterance.rate = 1;
    utterance.pitch = 1;
    utterance.volume = 1;
    speechSynthesis.speak(utterance);
  }
}
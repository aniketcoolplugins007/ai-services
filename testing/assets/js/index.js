class AIContentGenerator {
    constructor(formSelector) {
      this.form = document.querySelector(formSelector);
      this.promptInput = this.form.querySelector('#ai-prompt');
      this.serviceSelect = this.form.querySelector('#ai-service');
      this.generateButton = this.form.querySelector('#ai-generate-button');
      this.responseBox = this.form.querySelector('#ai-response');
  
      this.bindEvents();
    }
  
    bindEvents() {
      this.generateButton.addEventListener('click', (e) => {
        e.preventDefault();
        this.sendRequest();
      });
    }
  
    async sendRequest() {
      const prompt = this.promptInput.value.trim();
      const slug = this.serviceSelect.value;
  
      if (!prompt) {
        this.displayResponse('Please enter a prompt.');
        return;
      }
  
      this.displayResponse('Generating...');
  
      try {
        const response = await fetch(AI_Tab_Shortcode.ajaxurl, {
          method: 'POST',
          headers: {
            'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8',
          },
          body: new URLSearchParams({
            action: AI_Tab_Shortcode.action,
            slug: slug,
            strings: prompt,
            target_language: 'hi',
          })
        });
  
        const data = await response.json();
        this.displayResponse(JSON.stringify(data.data.translate_data) || 'No response from AI.');
      } catch (error) {
        console.error('AI Request Failed:', error);
        this.displayResponse('Something went wrong. Please try again.');
      }
    }
  
    displayResponse(message) {
      this.responseBox.innerHTML = message;
    }
  }
  
  // Init after DOM is loaded
  document.addEventListener('DOMContentLoaded', () => {
    new AIContentGenerator('.ai-content-generator');
  });
  
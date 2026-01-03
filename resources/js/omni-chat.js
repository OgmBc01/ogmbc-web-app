/* ===========================================
   OmniOGM Chat Assistant
   Version: 2.3
   Author: OGM Business Consultants
   Description: AI-powered chat assistant for business consulting
   Usage: Included in HTML and initialize with new omniChat()
   =========================================== */

class omniChat {
    constructor() {
        this.conversationHistory = [];
        this.usedPrompts = new Set();
        this.promptsCollapsed = false;
        this.isTypingResponse = false;
        this.isMaximized = false;
        this.thinking = null;
        this.overlay = null;

        this.availablePrompts = [
            "What business setup services do you offer?",
            "Tell me about UAE company formation",
            "How can I set up a company in USA?",
            "What accounting services do you provide?",
            "Do you offer tax consultancy services?",
            "What is your corporate tax expertise?",
            "Can you help with bank account opening?",
            "What audit services do you provide?",
            "Tell me about your IFRS advisory services",
            "Do you offer Golden Visa assistance?",
            "What are your office locations?",
            "How long does company formation take?",
            "What documents are needed for UAE setup?",
            "Do you provide ongoing compliance support?",
            "What industries do you serve?",
            "Can you help with business valuation?",
            "What is transfer pricing?",
            "Do you offer supply chain consulting?",
            "What corporate governance services do you provide?",
            "How can I contact OGMBC?",
            "What makes OGMBC different from others?",
            "Do you work with startups?",
            "What are your fees for company formation?",
            "Can you help with annual license renewal?",
            "What AML support do you provide?",
            "Do you offer bookkeeping services?",
            "What accounting software do you support?",
            "Can you help with due diligence?",
            "What internal control services do you offer?",
            "Do you provide management accounting?"
        ];

        // Initialize after DOM is ready
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', () => this.initialize());
        } else {
            this.initialize();
        }
    }

    /* =========================
       INITIALIZATION
       ========================= */

    initialize() {
        this.initElements();
        this.bindEvents();
        this.renderPrompts();
        this.createDownloadOverlay();
        this.injectClearButton();
        
        // Check if elements exist
        if (!this.widget) {
            console.error('Chat widget element not found');
            return;
        }
    }

    initElements() {
        this.widget = document.getElementById('omni-chat-widget');
        this.messages = document.getElementById('omni-messages');
        this.input = document.getElementById('omni-user-input');
        this.sendBtn = document.getElementById('omni-send-btn');
        this.floatBtn = document.getElementById('omni-floating-btn');
        this.closeBtn = document.getElementById('omni-close');
        this.status = document.getElementById('omni-status');

        this.promptsBox = document.getElementById('omni-prompts-container');
        this.togglePromptsBtn = document.getElementById('omni-toggle-prompts');
        this.refreshPromptsBtn = document.getElementById('omni-refresh-prompts');
        this.collapseIndicator = document.getElementById('omni-collapse-indicator');

        this.maximizeBtn = document.getElementById('omni-maximize-btn');
        this.downloadBtn = document.getElementById('omni-download-btn');
        this.charCount = document.querySelector('.omni-char-count');
    }

    bindEvents() {
        if (this.floatBtn) this.floatBtn.onclick = () => this.toggleChat();
        if (this.closeBtn) this.closeBtn.onclick = () => this.hideChatWidget();
        if (this.sendBtn) this.sendBtn.onclick = () => this.sendMessage();

        if (this.input) {
            this.input.oninput = () => {
                if (this.charCount) {
                    this.charCount.textContent = `${this.input.value.length}/500`;
                }
                this.input.style.height = 'auto';
                this.input.style.height = Math.min(this.input.scrollHeight, 120) + 'px';
            };

            this.input.onkeydown = e => {
                if (e.key === 'Enter' && !e.shiftKey) {
                    e.preventDefault();
                    this.sendMessage();
                }
            };
        }

        if (this.togglePromptsBtn) this.togglePromptsBtn.onclick = () => this.togglePrompts();
        if (this.refreshPromptsBtn) this.refreshPromptsBtn.onclick = () => this.renderPrompts(true);
        if (this.maximizeBtn) this.maximizeBtn.onclick = () => this.toggleMaximize();
        if (this.downloadBtn) this.downloadBtn.onclick = () => this.showDownloadModal();
    }

    /* =========================
       CHAT FUNCTIONS
       ========================= */

    toggleChat() {
        if (!this.widget) return;
        this.widget.classList.toggle('omni-hidden');
        if (this.status) {
            this.status.textContent = this.widget.classList.contains('omni-hidden') ? 'Offline' : 'Online';
        }
    }

    hideChatWidget() {
        if (!this.widget) return;
        this.widget.classList.add('omni-hidden');
        if (this.status) this.status.textContent = 'Offline';
    }

    async sendMessage(text = null) {
        let message = text;
        
        if (!message && this.input) {
            message = this.input.value.trim();
        }
        
        if (!message || message.length === 0) return;

        this.addMessage(message, true);
        this.conversationHistory.push({ role: 'user', content: message });

        if (this.input) {
            this.input.value = '';
            this.input.style.height = 'auto';
            if (this.charCount) {
                this.charCount.textContent = '0/500';
            }
        }

        this.showThinking();

        try {
            const res = await fetch('chat_proxy.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    message,
                    history: this.conversationHistory
                })
            });

            const data = await res.json();
            this.removeThinking();

            const clean = this.cleanResponse(data.reply);
            const el = this.addMessage('', false);
            this.typeWriter(el, clean);
            this.conversationHistory.push({ role: 'assistant', content: clean });

        } catch (error) {
            console.error('Chat error:', error);
            this.removeThinking();
            this.addMessage('Connection error. Please try again.', false);
        }
    }

    /* =========================
       RESPONSE PROCESSING
       ========================= */

    cleanResponse(text) {
        let cleaned = text
            .replace(/\.\.\.\s*\(truncated\)/gi, '')
            .replace(/\(truncated\)/gi, '')
            .trim();

        if (cleaned.endsWith('...')) {
            cleaned = cleaned.substring(0, cleaned.length - 3).trim();
        }

        return cleaned;
    }

    /* =========================
       MESSAGE HANDLING
       ========================= */

    addMessage(content, isUser) {
        if (!this.messages) {
            console.error('Messages container not found');
            return null;
        }

        const msg = document.createElement('div');
        msg.className = `omni-message ${isUser ? 'omni-message-user' : 'omni-message-bot'}`;

        const avatar = document.createElement('div');
        avatar.className = 'omni-message-avatar';
        avatar.innerHTML = isUser ? 'You' : `<img src="resources/img/omni.svg" width="32" alt="OmniOGM">`;

        const body = document.createElement('div');
        body.className = 'omni-message-content';
        if (isUser) body.textContent = content;

        msg.appendChild(avatar);
        msg.appendChild(body);
        this.messages.appendChild(msg);
        this.scrollBottom();

        return body;
    }

    typeWriter(el, text) {
        if (!el) return;
        
        el.innerHTML = '';
        let i = 0;
        const formatted = this.formatText(text);

        const step = () => {
            if (i <= formatted.length) {
                el.innerHTML = formatted.slice(0, i++);
                this.scrollBottom();
                requestAnimationFrame(step);
            }
        };
        step();
    }

    formatText(t) {
        if (!t) return '';
        
        let formatted = t
            .replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>')
            .replace(/(\n|^)- (.*?)(?=\n|$)/gm, '<li>$2</li>')
            .replace(/(\n|^)(\d+)\. (.*?)(?=\n|$)/gm, '<li>$3</li>')
            .replace(/\n/g, '<br>');

        formatted = formatted.replace(/(<li>.*?<\/li>(<br>)?)+/g, (match) => {
            return '<ul class="omni-list">' + match.replace(/<br>/g, '') + '</ul>';
        });

        return formatted;
    }

    /* =========================
       THINKING INDICATOR
       ========================= */

    showThinking() {
        if (!this.messages) return;
        
        this.thinking = document.createElement('div');
        this.thinking.className = 'omni-thinking';
        this.thinking.innerHTML = `
            <div class="omni-message-avatar"><img src="resources/img/omni.svg" width="32" alt="OmniOGM"></div>
            <div class="omni-thinking-text">Thinking...</div>`;
        this.messages.appendChild(this.thinking);
        this.scrollBottom();
    }

    removeThinking() {
        if (this.thinking && this.thinking.parentNode) {
            this.thinking.remove();
        }
    }

    scrollBottom() {
        if (this.messages) {
            this.messages.scrollTop = this.messages.scrollHeight;
        }
    }

    /* =========================
       PROMPTS MANAGEMENT
       ========================= */

    renderPrompts(shuffle = false) {
        if (!this.promptsBox) return;
        
        this.promptsBox.innerHTML = '';
        let prompts = [...this.availablePrompts];
        if (shuffle) prompts.sort(() => Math.random() - 0.5);

        const available = prompts.filter(p => !this.usedPrompts.has(p));
        const displayPrompts = available.slice(0, 3);

        if (displayPrompts.length < 3) {
            const used = prompts.filter(p => this.usedPrompts.has(p));
            displayPrompts.push(...used.slice(0, 3 - displayPrompts.length));
        }

        displayPrompts.forEach(p => {
            const btn = document.createElement('button');
            btn.className = 'omni-prompt-btn';
            btn.textContent = p;

            if (this.usedPrompts.has(p)) {
                btn.classList.add('omni-prompt-used');
                btn.disabled = true;
            }

            btn.onclick = () => {
                this.usedPrompts.add(p);
                this.sendMessage(p);
                btn.classList.add('omni-prompt-used');
                btn.disabled = true;
            };

            this.promptsBox.appendChild(btn);
        });
    }

    togglePrompts() {
        if (!this.promptsBox || !this.togglePromptsBtn || !this.collapseIndicator) return;
        
        this.promptsCollapsed = !this.promptsCollapsed;
        this.promptsBox.classList.toggle('omni-prompts-collapsed', this.promptsCollapsed);
        this.promptsBox.classList.toggle('omni-prompts-expanded', !this.promptsCollapsed);
        this.togglePromptsBtn.classList.toggle('collapsed', this.promptsCollapsed);
        this.collapseIndicator.textContent =
            this.promptsCollapsed ? '(click to expand)' : '(click to collapse)';
    }

    /* =========================
       VIEW CONTROLS
       ========================= */

    toggleMaximize() {
        if (!this.widget) return;
        this.isMaximized = !this.isMaximized;
        this.widget.classList.toggle('omni-maximized', this.isMaximized);
    }

    /* =========================
       CHAT MANAGEMENT
       ========================= */

    injectClearButton() {
        if (!this.downloadBtn || !this.downloadBtn.parentNode) return;
        
        if (document.querySelector('.omni-clear-btn')) return;

        const btn = document.createElement('button');
        btn.className = 'omni-action-btn omni-clear-btn';
        btn.title = 'Clear chat';
        btn.innerHTML = '🗑';
        btn.onclick = () => this.clearChat();

        const headerActions = this.downloadBtn.parentNode;
        headerActions.insertBefore(btn, this.maximizeBtn);
    }

    clearChat() {
        if (!this.messages) return;
        
        const welcomeMsg = this.messages.querySelector('.omni-welcome-message');
        this.messages.innerHTML = '';
        if (welcomeMsg) this.messages.appendChild(welcomeMsg);

        this.conversationHistory = [];
        this.usedPrompts.clear();
        this.renderPrompts(true);
        this.scrollBottom();
    }

    /* =========================
       PDF DOWNLOAD
       ========================= */

    createDownloadOverlay() {
        if (document.querySelector('.omni-download-overlay')) return;
        
        this.overlay = document.createElement('div');
        this.overlay.className = 'omni-download-overlay';
        this.overlay.innerHTML = `
            <div class="omni-download-modal">
                <h3 class="omni-download-title">Download Chat</h3>
                <div class="omni-download-actions">
                    <button class="omni-cancel-btn">Cancel</button>
                    <button class="omni-download-btn">Download PDF</button>
                </div>
            </div>`;
        document.body.appendChild(this.overlay);

        this.overlay.querySelector('.omni-cancel-btn').onclick = () =>
            this.overlay.classList.remove('active');

        this.overlay.querySelector('.omni-download-btn').onclick = () =>
            this.downloadPDF();
    }

    showDownloadModal() {
        if (!this.overlay) return;
        
        if (this.conversationHistory.length === 0) {
            alert('No conversation to download.');
            return;
        }
        this.overlay.classList.add('active');
    }

    async downloadPDF() {
        if (this.overlay) {
            this.overlay.classList.remove('active');
        }

        try {
            await this.loadJSPDF();
            
            if (!window.jspdf || !window.jspdf.jsPDF) {
                throw new Error('jsPDF not loaded');
            }

            const { jsPDF } = window.jspdf;
            const pdf = new jsPDF("p", "mm", "a4");

            const pageWidth = pdf.internal.pageSize.getWidth();
            const pageHeight = pdf.internal.pageSize.getHeight();

            const marginX = 18;
            let cursorY = 20;

            // Load logo for watermark
            let logoImg = null;
            try {
                logoImg = new Image();
                logoImg.crossOrigin = "Anonymous";
                logoImg.src = "resources/img/logo.png";
                
                await new Promise((resolve, reject) => {
                    logoImg.onload = resolve;
                    logoImg.onerror = () => reject(new Error("Failed to load logo"));
                });
            } catch (e) {
                console.warn("Could not load logo for watermark:", e);
                logoImg = null;
            }

            /**
             * Add watermark logo to the current page
             * Converts logo to semi-transparent canvas image for watermark effect
             */
            const addWatermark = () => {
                if (!logoImg) return;
                
                try {
                    const watermarkHeight = 160; // 4x larger for better visibility
                    const aspectRatio = logoImg.naturalWidth / logoImg.naturalHeight;
                    const watermarkWidth = watermarkHeight * aspectRatio;
                    
                    // Center watermark on page
                    const watermarkX = (pageWidth - watermarkWidth) / 2;
                    const watermarkY = (pageHeight - watermarkHeight) / 2;
                    
                    // Create canvas with semi-transparent image
                    const canvas = document.createElement('canvas');
                    canvas.width = logoImg.naturalWidth;
                    canvas.height = logoImg.naturalHeight;
                    
                    const ctx = canvas.getContext('2d');
                    ctx.globalAlpha = 0.15; // 15% opacity for better watermark visibility
                    ctx.drawImage(logoImg, 0, 0);
                    
                    // Convert canvas to image data and add to PDF
                    const watermarkImageData = canvas.toDataURL('image/png');
                    pdf.addImage(watermarkImageData, 'PNG', watermarkX, watermarkY, watermarkWidth, watermarkHeight);
                } catch (e) {
                    console.warn("Could not add watermark:", e);
                }
            };

            /* -------------------------------------------------------
              UTILITIES
            ------------------------------------------------------- */

            const addPageIfNeeded = (extraSpace = 10) => {
                if (cursorY + extraSpace > pageHeight - 20) {
                    pdf.addPage();
                    addWatermark(); // Add watermark to new page
                    cursorY = 20;
                }
            };

            const drawDivider = () => {
                cursorY += 4;
                pdf.setDrawColor(20, 45, 90);
                pdf.setLineWidth(0.6);
                pdf.line(marginX, cursorY, pageWidth - marginX, cursorY);
                cursorY += 8;
            };


            /* -------------------------------------------------------
              HEADER
            ------------------------------------------------------- */

            // Add company logo centered at top with correct aspect ratio
            try {
                const logoImg = new Image();
                logoImg.crossOrigin = "Anonymous";
                logoImg.src = "resources/img/logo.png";
                
                // Load image synchronously by waiting for it
                await new Promise((resolve, reject) => {
                    logoImg.onload = resolve;
                    logoImg.onerror = () => reject(new Error("Failed to load logo"));
                });
                
                // Calculate proportional dimensions based on actual image
                const targetHeight = 15; // Fixed height for consistent layout
                const aspectRatio = logoImg.naturalWidth / logoImg.naturalHeight;
                const logoWidth = targetHeight * aspectRatio; // Maintain aspect ratio
                const logoX = (pageWidth - logoWidth) / 2;
                
                pdf.addImage(logoImg, 'PNG', logoX, cursorY, logoWidth, targetHeight);
                cursorY += targetHeight + 8; // Add space after logo
            } catch (e) {
                console.warn("Could not load logo, using text header:", e);
                // Continue with text-only header if logo fails
            }

            // Header text
            pdf.setFont("helvetica", "bold");
            pdf.setFontSize(18);
            pdf.setTextColor(15, 35, 70);
            pdf.text("OmniOGM Assistant", pageWidth / 2, cursorY, { align: "center" });

            cursorY += 8;

            pdf.setFontSize(11);
            pdf.setFont("helvetica", "normal");
            pdf.setTextColor(90);
            pdf.text(
                "OGM Business Consultants – Conversation History",
                pageWidth / 2,
                cursorY,
                { align: "center" }
            );

            cursorY += 8;

            // Add watermark to first page
            addWatermark();

            pdf.setFontSize(9);
            pdf.setTextColor(130);
            pdf.text(
                `Downloaded on: ${new Date().toLocaleString()}`,
                pageWidth / 2,
                cursorY,
                { align: "center" }
            );

            cursorY += 10;
            drawDivider();

            /* -------------------------------------------------------
              CHAT CONTENT
            ------------------------------------------------------- */

            pdf.setFontSize(11);
            pdf.setTextColor(40);

            this.conversationHistory.forEach((msg, index) => {
                addPageIfNeeded(25);

                let role = msg.role === 'user' ? 'You' : 'OmniOGM';
                let roleColor = msg.role === 'user' ? [170, 120, 0] : [20, 45, 90];

                /* Role label */
                pdf.setFont("helvetica", "bold");
                pdf.setTextColor(...roleColor);
                pdf.text(`${role}:`, marginX, cursorY);
                cursorY += 6;

                /* Message text */
                pdf.setFont("helvetica", "normal");
                pdf.setTextColor(60);

                const rawText = this.cleanResponse(msg.content)
                    .replace(/\n{3,}/g, "\n\n")
                    .trim();

                const lines = rawText.split("\n");

                lines.forEach(line => {
                    addPageIfNeeded(8);

                    // Simple text wrapping
                    const textLines = pdf.splitTextToSize(line, pageWidth - marginX * 2);
                    pdf.text(textLines, marginX, cursorY);
                    cursorY += textLines.length * 6;
                });

                cursorY += 8;
            });

            /* -------------------------------------------------------
              FOOTER
            ------------------------------------------------------- */

            addPageIfNeeded(20);
            pdf.setDrawColor(220);
            pdf.line(marginX, cursorY, pageWidth - marginX, cursorY);
            cursorY += 8;

            pdf.setFontSize(9);
            pdf.setTextColor(120);
            pdf.text(
                "© 2025 OGM Business Consultants. All rights reserved.",
                pageWidth / 2,
                cursorY,
                { align: "center" }
            );

            cursorY += 5;
            pdf.text(
                "This conversation was generated by the OmniOGM Assistant.",
                pageWidth / 2,
                cursorY,
                { align: "center" }
            );

            /* -------------------------------------------------------
              SAVE FILE
            ------------------------------------------------------- */

            const timestamp = new Date()
                .toISOString()
                .replace(/[:.]/g, "-")
                .slice(0, 19);

            pdf.save(`OGMBC-Chat-${timestamp}.pdf`);

        } catch (error) {
            console.error('PDF generation error:', error);
            alert('Error generating PDF. Please try again.');
        }
    }

    loadJSPDF() {
        return new Promise((resolve, reject) => {
            if (window.jspdf && window.jspdf.jsPDF) {
                resolve();
                return;
            }
            
            const script = document.createElement('script');
            script.src = 'https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js';
            
            script.onload = () => {
                if (window.jspdf && window.jspdf.jsPDF) {
                    resolve();
                } else {
                    reject(new Error('jsPDF not loaded correctly'));
                }
            };
            
            script.onerror = reject;
            document.head.appendChild(script);
        });
    }

    /* =========================
       PUBLIC API METHODS
       ========================= */

    // Public methods that might be needed from outside
    getConversationHistory() {
        return [...this.conversationHistory];
    }

    resetChat() {
        this.clearChat();
    }

    showChat() {
        this.toggleChat();
    }

    hideChat() {
        this.hideChatWidget();
    }

    sendCustomMessage(message) {
        this.sendMessage(message);
    }
}

/* =========================
   GLOBAL INITIALIZATION
   ========================= */

// Initialize chat when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
    if (!window.omniChat) {
        window.omniChat = new omniChat();
    }
});

/* =========================
   MODULE EXPORTS
   ========================= */

// For ES6 modules
if (typeof module !== 'undefined' && module.exports) {
    module.exports = omniChat;
}

// For AMD/RequireJS
if (typeof define === 'function' && define.amd) {
    define('omniChat', [], function() {
        return omniChat;
    });
}
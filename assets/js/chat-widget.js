// Chat Widget JavaScript
class ChatWidget {
    constructor() {
        this.isOpen = false;
        this.lastMessageId = 0;
        this.checkInterval = null;
        this.apiUrl = this.getApiUrl();
        this.init();
    }

    getApiUrl() {
        // Dùng đường dẫn tuyệt đối
        return '/bccnan/chat_api_simple.php';
    }

    init() {
        this.createWidget();
        this.initSession();
        this.bindEvents();
        this.checkAdminOnline();
        this.startPolling();
    }

    createWidget() {
        const widget = document.createElement('div');
        widget.className = 'chat-widget';
        widget.innerHTML = `
            <button class="chat-button" id="chatButton">
                <i class="fas fa-comments"></i>
                <span class="chat-unread-badge" id="chatUnreadBadge" style="display: none;">0</span>
            </button>
            
            <div class="chat-window" id="chatWindow">
                <div class="chat-header">
                    <div class="chat-header-info">
                        <div class="chat-avatar">
                            <i class="fas fa-headset"></i>
                        </div>
                        <div class="chat-header-text">
                            <h3>Hỗ trợ trực tuyến</h3>
                            <div class="chat-status">
                                <span class="status-dot" id="statusDot"></span>
                                <span id="statusText">Đang kiểm tra...</span>
                            </div>
                        </div>
                    </div>
                    <button class="chat-close" id="chatClose">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                
                <div class="chat-messages" id="chatMessages">
                    <div class="chat-welcome">
                        <div class="chat-welcome-icon">👋</div>
                        <h4>Xin chào!</h4>
                        <p>Chúng tôi có thể giúp gì cho bạn?</p>
                    </div>
                </div>
                
                <div class="chat-input">
                    <input type="text" class="chat-input-field" id="chatInput" 
                           placeholder="Nhập tin nhắn..." maxlength="500">
                    <button class="chat-send-btn" id="chatSend">
                        <i class="fas fa-paper-plane"></i>
                    </button>
                </div>
            </div>
        `;

        document.body.appendChild(widget);
    }

    bindEvents() {
        const chatButton = document.getElementById('chatButton');
        const chatClose = document.getElementById('chatClose');
        const chatSend = document.getElementById('chatSend');
        const chatInput = document.getElementById('chatInput');

        chatButton.addEventListener('click', () => this.toggleChat());
        chatClose.addEventListener('click', () => this.closeChat());
        chatSend.addEventListener('click', () => this.sendMessage());

        chatInput.addEventListener('keypress', (e) => {
            if (e.key === 'Enter') {
                this.sendMessage();
            }
        });
    }

    async initSession() {
        try {
            const response = await fetch(this.apiUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'action=init'
            });

            const data = await response.json();
            if (data.success) {
                console.log('Chat session initialized:', data.session_id);
            }
        } catch (error) {
            console.error('Error initializing chat:', error);
        }
    }

    toggleChat() {
        this.isOpen = !this.isOpen;
        const chatWindow = document.getElementById('chatWindow');
        const chatButton = document.getElementById('chatButton');

        if (this.isOpen) {
            chatWindow.classList.add('active');
            chatButton.classList.add('active');
            this.showWelcomeMenu();
            this.clearUnreadBadge();
            document.getElementById('chatInput').focus();
        } else {
            chatWindow.classList.remove('active');
            chatButton.classList.remove('active');
        }
    }

    async showWelcomeMenu(reset = false) {
        const messagesContainer = document.getElementById('chatMessages');

        // Nếu reset hoặc chưa có tin nhắn
        if (reset || messagesContainer.children.length <= 1) {
            messagesContainer.innerHTML = '';

            // Lấy danh mục từ API
            try {
                const response = await fetch(this.apiUrl + '?action=get_categories');
                const data = await response.json();

                if (data.success) {
                    this.appendBotMessage('👋 Xin chào! Tôi là trợ lý ảo của KIENANSHOP.');
                    this.appendBotMessage('Tôi có thể giúp bạn tìm sản phẩm mô hình. Vui lòng chọn danh mục:');
                    this.appendCategoryButtons(data.categories);
                }
            } catch (error) {
                console.error('Error loading categories:', error);
            }
        }
    }

    resetChat() {
        this.showWelcomeMenu(true);
    }

    appendBotMessage(text) {
        const messagesContainer = document.getElementById('chatMessages');
        const messageDiv = document.createElement('div');
        messageDiv.className = 'message admin';
        messageDiv.innerHTML = `
            <div class="message-avatar">
                <i class="fas fa-robot"></i>
            </div>
            <div class="message-content">
                <div class="message-bubble">${text}</div>
                <div class="message-time">${new Date().toLocaleTimeString('vi-VN', { hour: '2-digit', minute: '2-digit' })}</div>
            </div>
        `;
        messagesContainer.appendChild(messageDiv);
        this.scrollToBottom();
    }

    appendCategoryButtons(categories) {
        const messagesContainer = document.getElementById('chatMessages');
        const buttonDiv = document.createElement('div');
        buttonDiv.className = 'message admin';

        let buttonsHtml = '<div class="chat-buttons">';
        categories.forEach(cat => {
            buttonsHtml += `<button class="chat-btn" onclick="chatWidget.selectCategory(${cat.id}, '${cat.name}')">${cat.name}</button>`;
        });
        buttonsHtml += '</div>';

        buttonDiv.innerHTML = `
            <div class="message-avatar">
                <i class="fas fa-robot"></i>
            </div>
            <div class="message-content">
                ${buttonsHtml}
            </div>
        `;
        messagesContainer.appendChild(buttonDiv);
        this.scrollToBottom();
    }

    async selectCategory(categoryId, categoryName) {
        this.appendUserMessage(`Tôi chọn: ${categoryName}`);

        try {
            const response = await fetch(this.apiUrl + `?action=get_products&category_id=${categoryId}`);
            const data = await response.json();

            if (data.success && data.products.length > 0) {
                this.appendBotMessage(`Đây là các sản phẩm ${categoryName}:`);
                this.appendProductButtons(data.products);
            } else {
                this.appendBotMessage('Xin lỗi, hiện chưa có sản phẩm trong danh mục này.');
            }
        } catch (error) {
            console.error('Error loading products:', error);
        }
    }

    appendProductButtons(products) {
        const messagesContainer = document.getElementById('chatMessages');
        const buttonDiv = document.createElement('div');
        buttonDiv.className = 'message admin';

        let buttonsHtml = '<div class="chat-buttons">';
        products.forEach(product => {
            const price = new Intl.NumberFormat('vi-VN').format(product.price);
            buttonsHtml += `<button class="chat-btn chat-btn-product" onclick="chatWidget.selectProduct(${product.id}, '${product.name}', ${product.price})">
                ${product.name}<br><small>${price}đ</small>
            </button>`;
        });
        buttonsHtml += '</div>';

        buttonDiv.innerHTML = `
            <div class="message-avatar">
                <i class="fas fa-robot"></i>
            </div>
            <div class="message-content">
                ${buttonsHtml}
            </div>
        `;
        messagesContainer.appendChild(buttonDiv);
        this.scrollToBottom();
    }

    selectProduct(productId, productName, price) {
        const priceFormatted = new Intl.NumberFormat('vi-VN').format(price);
        this.appendUserMessage(`Tôi quan tâm: ${productName}`);
        this.appendBotMessage(`Sản phẩm "${productName}" có giá ${priceFormatted}đ. Bạn có muốn xem chi tiết không?`);

        const messagesContainer = document.getElementById('chatMessages');
        const buttonDiv = document.createElement('div');
        buttonDiv.className = 'message admin';
        buttonDiv.innerHTML = `
            <div class="message-avatar">
                <i class="fas fa-robot"></i>
            </div>
            <div class="message-content">
                <div class="chat-buttons">
                    <a href="product-detail.php?id=${productId}" target="_blank" class="chat-btn chat-btn-primary">
                        <i class="fas fa-eye"></i> Xem chi tiết
                    </a>
                    <button class="chat-btn" onclick="chatWidget.resetChat()">
                        <i class="fas fa-arrow-left"></i> Chọn sản phẩm khác
                    </button>
                </div>
            </div>
        `;
        messagesContainer.appendChild(buttonDiv);
        this.scrollToBottom();
    }

    appendUserMessage(text) {
        const messagesContainer = document.getElementById('chatMessages');
        const messageDiv = document.createElement('div');
        messageDiv.className = 'message user';
        messageDiv.innerHTML = `
            <div class="message-avatar">
                <i class="fas fa-user"></i>
            </div>
            <div class="message-content">
                <div class="message-bubble">${text}</div>
                <div class="message-time">${new Date().toLocaleTimeString('vi-VN', { hour: '2-digit', minute: '2-digit' })}</div>
            </div>
        `;
        messagesContainer.appendChild(messageDiv);
        this.scrollToBottom();
    }

    closeChat() {
        this.isOpen = false;
        document.getElementById('chatWindow').classList.remove('active');
        document.getElementById('chatButton').classList.remove('active');
    }

    async sendMessage() {
        const input = document.getElementById('chatInput');
        const message = input.value.trim();

        if (!message) return;

        // Disable input
        input.disabled = true;
        document.getElementById('chatSend').disabled = true;

        try {
            const response = await fetch(this.apiUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `action=send&message=${encodeURIComponent(message)}`
            });

            const data = await response.json();

            if (data.success) {
                input.value = '';
                this.loadMessages();
            } else {
                alert(data.message || 'Lỗi gửi tin nhắn');
            }
        } catch (error) {
            console.error('Error sending message:', error);
            alert('Lỗi kết nối. Vui lòng thử lại.');
        } finally {
            input.disabled = false;
            document.getElementById('chatSend').disabled = false;
            input.focus();
        }
    }

    async loadMessages() {
        try {
            const response = await fetch(`${this.apiUrl}?action=get_messages&last_id=${this.lastMessageId}`);
            const data = await response.json();

            if (data.success && data.messages.length > 0) {
                const messagesContainer = document.getElementById('chatMessages');

                // Remove welcome message if exists
                const welcome = messagesContainer.querySelector('.chat-welcome');
                if (welcome) {
                    welcome.remove();
                }

                data.messages.forEach(msg => {
                    this.appendMessage(msg);
                    this.lastMessageId = Math.max(this.lastMessageId, msg.id);
                });

                this.scrollToBottom();
            }
        } catch (error) {
            console.error('Error loading messages:', error);
        }
    }

    appendMessage(msg) {
        const messagesContainer = document.getElementById('chatMessages');
        const messageDiv = document.createElement('div');
        messageDiv.className = `message ${msg.sender_type}`;

        const time = new Date(msg.created_at).toLocaleTimeString('vi-VN', {
            hour: '2-digit',
            minute: '2-digit'
        });

        if (msg.message_type === 'product_link' && msg.product_id) {
            messageDiv.innerHTML = `
                <div class="message-avatar">
                    <i class="fas fa-${msg.sender_type === 'admin' ? 'headset' : 'user'}"></i>
                </div>
                <div class="message-content">
                    <div class="product-link-message">
                        <img src="${msg.product_image}" alt="${msg.product_name}" class="product-link-image">
                        <div class="product-link-info">
                            <div class="product-link-name">${msg.product_name}</div>
                            <div class="product-link-price">${this.formatPrice(msg.product_price)}đ</div>
                            <a href="product-detail.php?id=${msg.product_id}" class="product-link-btn" target="_blank">
                                Xem sản phẩm
                            </a>
                        </div>
                    </div>
                    <div class="message-time">${time}</div>
                </div>
            `;
        } else {
            messageDiv.innerHTML = `
                <div class="message-avatar">
                    <i class="fas fa-${msg.sender_type === 'admin' ? 'headset' : 'user'}"></i>
                </div>
                <div class="message-content">
                    <div class="message-bubble">${this.escapeHtml(msg.message)}</div>
                    <div class="message-time">${time}</div>
                </div>
            `;
        }

        messagesContainer.appendChild(messageDiv);
    }

    scrollToBottom() {
        const messagesContainer = document.getElementById('chatMessages');
        messagesContainer.scrollTop = messagesContainer.scrollHeight;
    }

    async checkAdminOnline() {
        // Luôn hiển thị trực tuyến
        const statusDot = document.getElementById('statusDot');
        const statusText = document.getElementById('statusText');

        if (statusDot && statusText) {
            statusDot.classList.remove('offline');
            statusText.textContent = 'Trực tuyến';
        }
    }

    async checkUnreadMessages() {
        if (this.isOpen) return; // Don't check if chat is open

        try {
            const response = await fetch(`${this.apiUrl}?action=get_unread_count`);
            const data = await response.json();

            if (data.success && data.count > 0) {
                const badge = document.getElementById('chatUnreadBadge');
                badge.textContent = data.count;
                badge.style.display = 'flex';
            }
        } catch (error) {
            console.error('Error checking unread messages:', error);
        }
    }

    clearUnreadBadge() {
        const badge = document.getElementById('chatUnreadBadge');
        badge.style.display = 'none';
        badge.textContent = '0';
    }

    startPolling() {
        // Check for new messages every 3 seconds
        this.checkInterval = setInterval(() => {
            if (this.isOpen) {
                this.loadMessages();
            } else {
                this.checkUnreadMessages();
            }
        }, 3000);

        // Check admin status every 30 seconds
        setInterval(() => {
            this.checkAdminOnline();
        }, 30000);
    }

    formatPrice(price) {
        return new Intl.NumberFormat('vi-VN').format(price);
    }

    escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
}

// Initialize chat widget when DOM is ready
let chatWidget;
document.addEventListener('DOMContentLoaded', () => {
    chatWidget = new ChatWidget();
});

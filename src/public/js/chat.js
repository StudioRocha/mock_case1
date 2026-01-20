(function() {
    'use strict';
    
    // ============================================
    // 初期化: データ属性から設定値を取得
    // ============================================
    const chatContainer = document.querySelector('.p-chat');
    if (!chatContainer) return;
    
    const config = {
        isBuyer: chatContainer.dataset.isBuyer === '1',
        saveDraftUrl: chatContainer.dataset.saveDraftUrl,
        csrfToken: chatContainer.dataset.csrfToken,
        updateUrlTemplate: chatContainer.dataset.updateUrlTemplate,
        destroyUrlTemplate: chatContainer.dataset.destroyUrlTemplate
    };
    
    // ============================================
    // ユーティリティ関数
    // ============================================
    
    /**
     * 入力情報をセッションに保存
     */
    function saveDraftMessage(message) {
        fetch(config.saveDraftUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': config.csrfToken
            },
            body: JSON.stringify({ message: message })
        });
    }
    
    /**
     * モーダルを表示
     */
    function showModal(modalId) {
        const modal = document.getElementById(modalId);
        if (modal) {
            modal.style.display = 'flex';
        }
    }
    
    /**
     * モーダルを非表示
     */
    function hideModal(modalId) {
        const modal = document.getElementById(modalId);
        if (modal) {
            modal.style.display = 'none';
        }
    }
    
    // ============================================
    // 入力情報保持機能
    // ============================================
    const messageInput = document.querySelector('input[name="message"]');
    let saveTimeout = null;
    
    if (messageInput) {
        // 入力中: 500ms後に自動保存
        messageInput.addEventListener('input', function() {
            clearTimeout(saveTimeout);
            saveTimeout = setTimeout(function() {
                saveDraftMessage(messageInput.value);
            }, 500);
        });
        
        // フォーカスアウト時: 即座に保存
        messageInput.addEventListener('blur', function() {
            clearTimeout(saveTimeout);
            saveDraftMessage(messageInput.value);
        });
    }
    
    // ============================================
    // 取引完了機能（購入者のみ）
    // ============================================
    if (config.isBuyer) {
        const completeBtn = document.getElementById('completeTransactionBtn');
        const cancelCompleteBtn = document.getElementById('cancelCompleteBtn');
        
        if (completeBtn) {
            completeBtn.addEventListener('click', function() {
                showModal('completeModal');
            });
        }
        
        if (cancelCompleteBtn) {
            cancelCompleteBtn.addEventListener('click', function() {
                hideModal('completeModal');
            });
        }
    }
    
    // ============================================
    // メッセージ編集機能
    // ============================================
    document.querySelectorAll('.p-chat__message-edit').forEach(btn => {
        btn.addEventListener('click', function() {
            const messageId = this.dataset.messageId;
            const messageContent = this.closest('.p-chat__message-content');
            const messageBubble = messageContent.querySelector('.p-chat__message-bubble');
            const messageText = messageBubble.textContent.trim();
            
            const editForm = document.getElementById('editMessageForm');
            const editTextarea = document.getElementById('editModal').querySelector('textarea[name="message"]');
            
            editForm.action = config.updateUrlTemplate.replace(':messageId', messageId);
            editTextarea.value = messageText;
            showModal('editModal');
        });
    });
    
    const cancelEditBtn = document.getElementById('cancelEditBtn');
    if (cancelEditBtn) {
        cancelEditBtn.addEventListener('click', function() {
            hideModal('editModal');
        });
    }
    
    // ============================================
    // メッセージ削除機能
    // ============================================
    document.querySelectorAll('.p-chat__message-delete').forEach(btn => {
        btn.addEventListener('click', function() {
            if (!confirm('このメッセージを削除しますか？')) {
                return;
            }
            
            const messageId = this.dataset.messageId;
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = config.destroyUrlTemplate.replace(':messageId', messageId);
            
            // CSRFトークンを追加
            const csrfInput = document.createElement('input');
            csrfInput.type = 'hidden';
            csrfInput.name = '_token';
            csrfInput.value = config.csrfToken;
            form.appendChild(csrfInput);
            
            // DELETEメソッドを追加
            const methodInput = document.createElement('input');
            methodInput.type = 'hidden';
            methodInput.name = '_method';
            methodInput.value = 'DELETE';
            form.appendChild(methodInput);
            
            document.body.appendChild(form);
            form.submit();
        });
    });
})();

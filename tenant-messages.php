<?php require_once 'config.php';

if (!isLoggedIn()) {
    redirect('index.php');
}

// Sample conversations data for tenant
$conversations = [
    ['id' => 1, 'name' => 'Property Management', 'avatar' => 'A', 'lastMessage' => 'Your maintenance request has been scheduled for tomorrow.', 'time' => '2h', 'unread' => true, 'online' => true],
    ['id' => 2, 'name' => 'John - Maintenance', 'avatar' => 'J', 'lastMessage' => 'The technician will arrive between 2-4 PM.', 'time' => '4h', 'unread' => false, 'online' => false],
    ['id' => 3, 'name' => 'Sarah - Front Desk', 'avatar' => 'S', 'avatarClass' => 'green', 'lastMessage' => 'Your package is ready for pickup.', 'time' => '1d', 'unread' => false, 'online' => true],
];

// Sample messages for the selected conversation
$messages = [
    ['sender' => 'Property Management', 'avatar' => 'A', 'content' => 'Hello! How can we help you today?', 'time' => '10:00 AM', 'sent' => false],
    ['sender' => 'You', 'content' => 'Hi, the AC unit in my apartment isn\'t cooling properly.', 'time' => '10:15 AM', 'sent' => true],
    ['sender' => 'Property Management', 'avatar' => 'A', 'content' => 'I\'m sorry to hear that. Let me submit a maintenance request for you. Can you describe the issue in more detail?', 'time' => '10:20 AM', 'sent' => false],
    ['sender' => 'You', 'content' => 'The air is blowing but it\'s not cold. It seems like the compressor isn\'t working.', 'time' => '10:25 AM', 'sent' => true],
    ['sender' => 'Property Management', 'avatar' => 'A', 'content' => 'Thank you for the details. I\'ve submitted a maintenance request and a technician will contact you within 24 hours to schedule a visit.', 'time' => '10:30 AM', 'sent' => false],
    ['sender' => 'You', 'content' => 'That\'s great, thank you!', 'time' => '10:32 AM', 'sent' => true],
    ['sender' => 'Property Management', 'avatar' => 'A', 'content' => 'Your maintenance request has been scheduled for tomorrow. The technician will arrive between 2 PM and 4 PM.', 'time' => '2:00 PM', 'sent' => false],
];

$unreadCount = count(array_filter($conversations, fn($c) => $c['unread']));
$selectedConversation = $conversations[0];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Messages - <?php echo SITE_NAME; ?> Tenant</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        .tenant-wrapper { display: flex; min-height: 100vh; }
        .sidebar { width: 260px; background: #1f2937; color: white; position: fixed; height: 100vh; overflow-y: auto; z-index: 100; }
        .sidebar-header { padding: 25px 20px; border-bottom: 1px solid #374151; }
        .sidebar-header .logo { font-size: 1.5rem; font-weight: 700; color: white; margin-bottom: 5px; }
        .sidebar-header .logo span { color: #60a5fa; }
        .sidebar-header p { font-size: 0.85rem; color: #9ca3af; }
        .sidebar-nav { padding: 20px 0; }
        .nav-section { margin-bottom: 25px; }
        .nav-section-title { padding: 0 20px; font-size: 0.75rem; text-transform: uppercase; letter-spacing: 1px; color: #6b7280; margin-bottom: 10px; }
        .sidebar-link { display: flex; align-items: center; gap: 12px; padding: 12px 20px; color: #d1d5db; text-decoration: none; transition: var(--transition); cursor: pointer; border-left: 3px solid transparent; }
        .sidebar-link:hover, .sidebar-link.active { background: #374151; color: white; border-left-color: #60a5fa; }
        .sidebar-link .icon { font-size: 1.2rem; width: 24px; text-align: center; }
        .sidebar-link .badge { margin-left: auto; background: #ef4444; color: white; font-size: 0.75rem; padding: 2px 8px; border-radius: 10px; }
        .sidebar-footer { position: absolute; bottom: 0; left: 0; right: 0; padding: 20px; border-top: 1px solid #374151; background: #1f2937; }
        .user-info { display: flex; align-items: center; gap: 12px; }
        .user-avatar { width: 40px; height: 40px; background: var(--primary-color); border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: 600; }
        .user-details strong { display: block; font-size: 0.9rem; }
        .user-details span { font-size: 0.8rem; color: #9ca3af; }
        .main-content { flex: 1; margin-left: 260px; background: #f3f4f6; }
        .top-header { background: white; padding: 15px 30px; display: flex; justify-content: space-between; align-items: center; box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1); position: sticky; top: 0; z-index: 50; }
        .header-left h1 { font-size: 1.5rem; color: var(--text-dark); margin-bottom: 3px; }
        .header-left p { font-size: 0.9rem; color: var(--text-light); }
        .header-right { display: flex; align-items: center; gap: 15px; }
        .header-btn { width: 40px; height: 40px; border-radius: 50%; border: none; background: #f3f4f6; cursor: pointer; display: flex; align-items: center; justify-content: center; font-size: 1.2rem; transition: var(--transition); position: relative; }
        .page-content { padding: 30px; }
        .notification-btn { position: relative; }
        .notification-badge { position: absolute; top: -5px; right: -5px; width: 18px; height: 18px; background: #ef4444; border-radius: 50%; font-size: 0.7rem; display: flex; align-items: center; justify-content: center; color: white; }
        
        .messages-container { display: grid; grid-template-columns: 300px 1fr; gap: 25px; height: calc(100vh - 200px); min-height: 500px; }
        .conversations-list { background: white; border-radius: 12px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); overflow: hidden; display: flex; flex-direction: column; }
        .conversations-header { padding: 20px; border-bottom: 1px solid #e5e7eb; display: flex; justify-content: space-between; align-items: center; }
        .conversations-header h3 { font-size: 1.1rem; font-weight: 600; color: var(--text-dark); }
        .search-box { padding: 15px; border-bottom: 1px solid #e5e7eb; }
        .search-box input { width: 100%; padding: 10px 15px; border: 2px solid #e5e7eb; border-radius: 8px; font-size: 0.9rem; }
        .search-box input:focus { outline: none; border-color: var(--primary-color); }
        .conversations { flex: 1; overflow-y: auto; }
        .conversation { display: flex; gap: 12px; padding: 15px 20px; border-bottom: 1px solid #e5e7eb; cursor: pointer; transition: var(--transition); }
        .conversation:hover { background: #f9fafb; }
        .conversation.active { background: #eff6ff; border-left: 3px solid var(--primary-color); }
        .conversation.unread { background: #f0f9ff; }
        .avatar { width: 48px; height: 48px; background: var(--primary-color); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-weight: 600; flex-shrink: 0; }
        .avatar.blue { background: #3b82f6; }
        .avatar.green { background: #10b981; }
        .avatar.purple { background: #8b5cf6; }
        .conversation-info { flex: 1; min-width: 0; }
        .conversation-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 4px; }
        .conversation-name { font-weight: 600; font-size: 0.95rem; color: var(--text-dark); }
        .conversation-time { font-size: 0.75rem; color: #9ca3af; }
        .conversation-preview { font-size: 0.85rem; color: var(--text-light); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .conversation.unread .conversation-name { color: var(--primary-color); }
        .unread-dot { width: 8px; height: 8px; background: var(--primary-color); border-radius: 50%; flex-shrink: 0; }
        
        .chat-area { background: white; border-radius: 12px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); display: flex; flex-direction: column; }
        .chat-header { padding: 20px; border-bottom: 1px solid #e5e7eb; display: flex; justify-content: space-between; align-items: center; }
        .chat-user { display: flex; align-items: center; gap: 12px; }
        .chat-user .avatar { width: 44px; height: 44px; }
        .chat-user-info h3 { font-size: 1rem; font-weight: 600; color: var(--text-dark); margin-bottom: 3px; }
        .chat-user-info p { font-size: 0.85rem; color: #10b981; }
        .chat-actions { display: flex; gap: 10px; }
        .chat-action-btn { width: 40px; height: 40px; border-radius: 50%; border: none; background: #f3f4f6; cursor: pointer; display: flex; align-items: center; justify-content: center; font-size: 1.1rem; transition: var(--transition); }
        .chat-action-btn:hover { background: #e5e7eb; }
        .chat-messages { flex: 1; padding: 20px; overflow-y: auto; background: #f9fafb; }
        .message { display: flex; gap: 12px; margin-bottom: 20px; max-width: 70%; }
        .message.sent { margin-left: auto; flex-direction: row-reverse; }
        .message-content { background: white; padding: 12px 16px; border-radius: 16px; box-shadow: 0 1px 2px rgba(0,0,0,0.05); }
        .message.sent .message-content { background: var(--primary-color); color: white; }
        .message-text { font-size: 0.95rem; line-height: 1.5; }
        .message-time { font-size: 0.75rem; color: #9ca3af; margin-top: 5px; }
        .message.sent .message-time { color: rgba(255,255,255,0.7); }
        .message-meta { display: flex; align-items: center; gap: 8px; margin-bottom: 8px; }
        .message-sender { font-weight: 600; font-size: 0.85rem; color: var(--text-dark); }
        .message.sent .message-sender { color: rgba(255,255,255,0.9); }
        
        .compose-area { padding: 20px; border-top: 1px solid #e5e7eb; background: white; border-radius: 0 0 12px 12px; }
        .compose-input { display: flex; gap: 12px; align-items: flex-end; }
        .compose-input textarea { flex: 1; padding: 12px 16px; border: 2px solid #e5e7eb; border-radius: 12px; font-size: 0.95rem; resize: none; min-height: 50px; max-height: 120px; }
        .compose-input textarea:focus { outline: none; border-color: var(--primary-color); }
        .compose-btn { width: 50px; height: 50px; border-radius: 50%; border: none; background: var(--primary-color); color: white; cursor: pointer; display: flex; align-items: center; justify-content: center; font-size: 1.3rem; transition: var(--transition); }
        .compose-btn:hover { background: var(--primary-dark); transform: scale(1.05); }
        .compose-actions { display: flex; gap: 10px; margin-bottom: 12px; }
        .compose-action { width: 36px; height: 36px; border-radius: 50%; border: none; background: #f3f4f6; cursor: pointer; display: flex; align-items: center; justify-content: center; font-size: 1rem; transition: var(--transition); }
        .compose-action:hover { background: #e5e7eb; }
        
        .logout-btn { display: flex; align-items: center; gap: 10px; width: 100%; padding: 10px 20px; background: none; border: none; color: #f87171; cursor: pointer; font-size: 0.95rem; transition: var(--transition); margin-top: 10px; }
        .logout-btn:hover { background: #374151; }
        .mobile-menu-toggle { display: none; position: fixed; bottom: 20px; right: 20px; width: 50px; height: 50px; background: var(--primary-color); color: white; border: none; border-radius: 50%; font-size: 1.5rem; cursor: pointer; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1); z-index: 200; }
        @media (max-width: 992px) { .sidebar { transform: translateX(-100%); } .sidebar.show { transform: translateX(0); } .main-content { margin-left: 0; } .mobile-menu-toggle { display: flex; align-items: center; justify-content: center; } .messages-container { grid-template-columns: 1fr; } }
        @media (max-width: 768px) { .page-content { padding: 15px; } .messages-container { height: auto; min-height: auto; } .conversations-list { max-height: 400px; } }
    </style>
</head>
<body>
    <div class="tenant-wrapper">
        <aside class="sidebar" id="sidebar">
            <div class="sidebar-header">
                <div class="logo"><?php echo SITE_NAME; ?><span>Hub</span></div>
                <p>Tenant Portal</p>
            </div>
            <nav class="sidebar-nav">
                <div class="nav-section">
                    <div class="nav-section-title">Overview</div>
                    <a href="tenant-dashboard.php" class="sidebar-link">
                        <span class="icon">🏠</span>
                        Dashboard
                    </a>
                </div>
                <div class="nav-section">
                    <div class="nav-section-title">Find Home</div>
                    <a href="tenant-listings.php" class="sidebar-link">
                        <span class="icon">🔍</span>
                        Listings
                    </a>
                    <a href="tenant-vacancies.php" class="sidebar-link">
                        <span class="icon">🚪</span>
                        Vacancies
                    </a>
                </div>
                <div class="nav-section">
                    <div class="nav-section-title">My Account</div>
                    <a href="tenant-scheduled-tours.php" class="sidebar-link">
                        <span class="icon">📅</span>
                        Scheduled Tours
                    </a>
                    <a href="tenant-messages.php" class="sidebar-link active">
                        <span class="icon">💬</span>
                        Messages
                        <?php if ($unreadCount > 0): ?>
                        <span class="badge"><?php echo $unreadCount; ?></span>
                        <?php endif; ?>
                    </a>
                    <a href="tenant-notifications.php" class="sidebar-link">
                        <span class="icon">🔔</span>
                        Notifications
                    </a>
                    <a href="tenant-payroll.php" class="sidebar-link">
                        <span class="icon">💰</span>
                        Payroll
                    </a>
                </div>
            </nav>
            <div class="sidebar-footer">
                <div class="user-info">
                    <div class="user-avatar">T</div>
                    <div class="user-details">
                        <strong>Tenant</strong>
                        <span>tenant@email.com</span>
                    </div>
                </div>
                <button class="logout-btn" onclick="window.location.href='index.php?logout=1'">
                    <span>🚪</span> Logout
                </button>
            </div>
        </aside>
        <main class="main-content">
            <header class="top-header">
                <div class="header-left">
                    <h1>Messages</h1>
                    <p>Contact property management</p>
                </div>
                <div class="header-right">
                    <button class="header-btn notification-btn" onclick="window.location.href='tenant-notifications.php'">
                        🔔
                        <span class="notification-badge">3</span>
                    </button>
                    <button class="header-btn" onclick="toggleSidebar()">☰</button>
                </div>
            </header>
            <div class="page-content">
                <div class="messages-container">
                    <div class="conversations-list">
                        <div class="conversations-header">
                            <h3>Conversations</h3>
                            <button style="background: none; border: none; font-size: 1.2rem; cursor: pointer;" onclick="showComposeModal()">✏️</button>
                        </div>
                        <div class="search-box">
                            <input type="text" placeholder="Search messages..." onkeyup="searchMessages(this.value)">
                        </div>
                        <div class="conversations" id="conversationsList">
                            <?php foreach ($conversations as $conv): ?>
                            <div class="conversation <?php echo $conv['unread'] ? 'unread' : ''; ?> <?php echo $conv['id'] === $selectedConversation['id'] ? 'active' : ''; ?>" onclick="selectConversation(<?php echo $conv['id']; ?>)">
                                <div class="avatar <?php echo sanitize($conv['avatarClass'] ?? ''); ?>"><?php echo sanitize($conv['avatar']); ?></div>
                                <div class="conversation-info">
                                    <div class="conversation-header">
                                        <span class="conversation-name"><?php echo sanitize($conv['name']); ?></span>
                                        <span class="conversation-time"><?php echo sanitize($conv['time']); ?></span>
                                    </div>
                                    <p class="conversation-preview"><?php echo sanitize($conv['lastMessage']); ?></p>
                                </div>
                                <?php if ($conv['unread']): ?>
                                <div class="unread-dot"></div>
                                <?php endif; ?>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    
                    <div class="chat-area" id="chatArea">
                        <div class="chat-header">
                            <div class="chat-user">
                                <div class="avatar <?php echo sanitize($selectedConversation['avatarClass'] ?? ''); ?>"><?php echo sanitize($selectedConversation['avatar']); ?></div>
                                <div class="chat-user-info">
                                    <h3><?php echo sanitize($selectedConversation['name']); ?></h3>
                                    <p><?php echo $selectedConversation['online'] ? '● Online' : '○ Offline'; ?></p>
                                </div>
                            </div>
                            <div class="chat-actions">
                                <button class="chat-action-btn" title="Video Call">📹</button>
                                <button class="chat-action-btn" title="Phone Call">📞</button>
                                <button class="chat-action-btn" title="More Options">⋮</button>
                            </div>
                        </div>
                        <div class="chat-messages" id="chatMessages">
                            <?php foreach ($messages as $msg): ?>
                            <div class="message <?php echo $msg['sent'] ? 'sent' : ''; ?>">
                                <?php if (!$msg['sent']): ?>
                                <div class="avatar" style="width: 36px; height: 36px; font-size: 0.9rem;"><?php echo sanitize($msg['avatar']); ?></div>
                                <?php endif; ?>
                                <div class="message-content">
                                    <?php if (!$msg['sent']): ?>
                                    <div class="message-meta">
                                        <span class="message-sender"><?php echo sanitize($msg['sender']); ?></span>
                                    </div>
                                    <?php endif; ?>
                                    <p class="message-text"><?php echo sanitize($msg['content']); ?></p>
                                    <span class="message-time"><?php echo sanitize($msg['time']); ?></span>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        <div class="compose-area">
                            <div class="compose-actions">
                                <button class="compose-action" title="Attach File">📎</button>
                                <button class="compose-action" title="Send Image">🖼️</button>
                                <button class="compose-action" title="Quick Reply">⚡</button>
                            </div>
                            <div class="compose-input">
                                <textarea id="messageInput" placeholder="Type a message..." onkeypress="handleKeyPress(event)"></textarea>
                                <button class="compose-btn" onclick="sendMessage()">➤</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
    
    <button class="mobile-menu-toggle" onclick="toggleSidebar()">☰</button>
    
    <script>
        let currentConversation = <?php echo $selectedConversation['id']; ?>;
        const conversations = <?php echo json_encode($conversations); ?>;
        
        function toggleSidebar() {
            document.getElementById('sidebar').classList.toggle('show');
        }
        
        function selectConversation(id) {
            currentConversation = id;
            document.querySelectorAll('.conversation').forEach(c => {
                c.classList.remove('active', 'unread');
            });
            event.currentTarget.classList.add('active');
            event.currentTarget.classList.remove('unread');
            
            const conv = conversations.find(c => c.id === id);
            if (conv) {
                document.querySelector('.chat-user .avatar').textContent = conv.avatar;
                document.querySelector('.chat-user .avatar').className = 'avatar ' + (conv.avatarClass || '');
                document.querySelector('.chat-user-info h3').textContent = conv.name;
                document.querySelector('.chat-user-info p').textContent = conv.online ? '● Online' : '○ Offline';
            }
            showToast('Loading conversation...', 'success');
        }
        
        function sendMessage() {
            const input = document.getElementById('messageInput');
            const message = input.value.trim();
            if (!message) return;
            
            const messagesContainer = document.getElementById('chatMessages');
            const messageHTML = `
                <div class="message sent">
                    <div class="message-content">
                        <p class="message-text">${message}</p>
                        <span class="message-time">${getCurrentTime()} ✓</span>
                    </div>
                </div>
            `;
            messagesContainer.insertAdjacentHTML('beforeend', messageHTML);
            input.value = '';
            scrollToBottom();
        }
        
        function handleKeyPress(event) {
            if (event.key === 'Enter' && !event.shiftKey) {
                event.preventDefault();
                sendMessage();
            }
        }
        
        function scrollToBottom() {
            const container = document.getElementById('chatMessages');
            container.scrollTop = container.scrollHeight;
        }
        
        function getCurrentTime() {
            const now = new Date();
            return now.toLocaleTimeString('en-US', { hour: 'numeric', minute: '2-digit' });
        }
        
        function searchMessages(query) {
            showToast('Searching messages...', 'success');
        }
        
        function showComposeModal() {
            showToast('Opening new message...', 'success');
        }
        
        function showToast(message, type = 'success') {
            const toast = document.createElement('div');
            toast.className = 'toast ' + type;
            toast.style.cssText = 'position:fixed;bottom:30px;right:30px;padding:15px 25px;border-radius:8px;z-index:3000;background:' + (type === 'success' ? '#10b981' : '#ef4444') + ';color:white;box-shadow:0 4px 6px rgba(0,0,0,0.1);';
            toast.textContent = message;
            document.body.appendChild(toast);
            setTimeout(() => { toast.style.transform = 'translateX(150%)'; setTimeout(() => toast.remove(), 300); }, 3000);
        }
        
        document.addEventListener('DOMContentLoaded', function() {
            scrollToBottom();
        });
    </script>
</body>
</html>
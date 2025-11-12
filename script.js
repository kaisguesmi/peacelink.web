// --- 1. Global Variables & Firebase Initialization ---

// These are passed from index.html
const FIREBASE_CONFIG = window.firebaseConfig;
const INITIAL_AUTH_TOKEN = window.initialAuthToken;
const APP_ID = window.appId;

let db, auth, userId;
let currentChatRecipientId = null; // Track who we are chatting with
let currentChatUnsubscribe = null; // Function to stop listening to messages
let isMockMode = false; // Is the app running in local "Mock Mode"?

// DOM Element cache
const sidebar = document.getElementById('sidebar');
const mainContent = document.getElementById('main-content');
const menuToggle = document.getElementById('menu-toggle');

// Wait for the DOM to be fully loaded before running app logic
document.addEventListener('DOMContentLoaded', initApp);

/**
 * Main initialization function.
 * Sets up Firebase, signs in the user, and attaches listeners.
 */
function initApp() {
    // --- MOCK MODE CHECK ---
    if (!FIREBASE_CONFIG || Object.keys(FIREBASE_CONFIG).length === 0) {
        console.warn("--- FIREBASE IS MISSING ---");
        console.warn("Running in MOCK MODE. All data is fake.");
        isMockMode = true;
        initializeAppWithMockData();
        initEventListeners(); 
        return; 
    }
    // --- END MOCK MODE CHECK ---

    try {
        firebase.initializeApp(FIREBASE_CONFIG);
        auth = firebase.auth();
        db = firebase.firestore();
        firebase.firestore().setLogLevel('debug');
        signInUser();
        initEventListeners();
    } catch (error) {
        console.error("Error initializing Firebase:", error);
        showNotification(`Failed to initialize application: ${error.message}`, 'error');
    }
}

/**
 * Handles signing in the user with the provided token or anonymously.
 */
async function signInUser() {
    try {
        if (INITIAL_AUTH_TOKEN) {
            await auth.signInWithCustomToken(INITIAL_AUTH_TOKEN);
        } else {
            await auth.signInAnonymously();
        }

        auth.onAuthStateChanged(user => {
            if (user) {
                userId = user.uid;
                console.log("User signed in with ID:", userId);
                document.getElementById('current-user-id-display').textContent = userId;
                
                // Load all app data
                loadUserData();
                loadAllUsers();
                loadChatHistory(); // Loads conversation list
                loadModerationData(); 
                loadDashboardStats(); 
                loadStories();
                loadInitiatives();
                loadStatistics();
                
            } else {
                console.log("User is signed out.");
                userId = null;
            }
        });
    } catch (error) {
        console.error("Error signing in:", error);
        showNotification(`Authentication failed: ${error.message}`, 'error');
    }
}

/**
 * Attaches global event listeners (navigation, forms, etc.).
 */
function initEventListeners() {
    // Sidebar navigation
    document.querySelectorAll('.nav-item').forEach(item => {
        item.addEventListener('click', () => {
            const pageId = item.getAttribute('data-page');
            if (pageId) {
                showPage(pageId);
            }
        });
    });

    // Sidebar toggle
    menuToggle.addEventListener('click', () => {
        sidebar.classList.toggle('collapsed');
        mainContent.classList.toggle('collapsed');
        
        if (window.innerWidth <= 992) {
             sidebar.classList.toggle('toggled');
        }
    });

    // Settings form
    const settingsForm = document.getElementById('settings-form');
    if (settingsForm) {
        settingsForm.addEventListener('submit', handleSaveSettings);
    }
    
    // Chat send form
    const chatForm = document.getElementById('chat-send-form');
    if (chatForm) {
        chatForm.addEventListener('submit', handleSendMessage);
    }

    // Notification modal close
    document.getElementById('notification-modal-close').addEventListener('click', () => {
        document.getElementById('notification-modal').style.display = 'none';
    });

    // *** BUG FIX (DELETE MESSAGE) ***
    // Use event delegation on the chat container
    const chatContainer = document.getElementById('chat-messages-container');
    if (chatContainer) {
        chatContainer.addEventListener('click', handleDeleteMessageClick);
    }
}

// --- 2. Page Navigation ---

/**
 * Shows a specific page and hides all others.
 * @param {string} pageId - The ID of the page element to show.
 */
function showPage(pageId) {
    document.querySelectorAll('.page-content').forEach(page => {
        page.classList.remove('active');
    });
    document.querySelectorAll('.nav-item').forEach(item => {
        item.classList.remove('active');
    });

    const targetPage = document.getElementById(pageId);
    if (targetPage) {
        targetPage.classList.add('active');
        
        if (pageId === 'page-statistics') {
            loadStatistics(); // Will draw mock charts if in mock mode
        }
    }

    const targetNavItem = document.querySelector(`.nav-item[data-page="${pageId}"]`);
    if (targetNavItem) {
        targetNavItem.classList.add('active');
    }
}

// --- 3. Firestore Data Logic (Users & Settings) ---

/**
 * Loads the current user's profile data (name, role).
 */
async function loadUserData() {
    if (!userId) return;
    const userDocRef = db.collection(getCollectionPath('users')).doc(userId);
    
    try {
        const doc = await userDocRef.get();
        if (doc.exists) {
            const data = doc.data();
            updateUIWithUserData(data);
        } else {
            const defaultData = { name: "New User", role: "student", status: "online", lastSeen: firebase.firestore.FieldValue.serverTimestamp() };
            await userDocRef.set(defaultData);
            updateUIWithUserData(defaultData);
        }
    } catch (error) {
        console.error("Error loading user data:", error);
        showNotification("Could not load your profile.", 'error');
    }
}

/**
 * Updates the UI (sidebar, settings page) with the user's data.
 * @param {object} data - User data object from Firestore.
 */
function updateUIWithUserData(data) {
    document.getElementById('current-user-name').textContent = data.name || "No Name";
    document.getElementById('current-user-role').textContent = data.role || "N/A";
    document.getElementById('profile-name').value = data.name || "";
    document.getElementById('profile-role').value = data.role || "student";

    if (data.role === 'admin') {
        document.getElementById('nav-reports').style.display = 'flex';
    } else {
        document.getElementById('nav-reports').style.display = 'none';
    }
}

/**
 * Handles saving the user's settings (profile).
 * @param {Event} event - The form submit event.
 */
async function handleSaveSettings(event) {
    event.preventDefault();
    const newName = document.getElementById('profile-name').value;
    const newRole = document.getElementById('profile-role').value;

    if (isMockMode) {
        console.log("Mock Save:", { name: newName, role: newRole });
        updateUIWithUserData({ name: newName, role: newRole }); // BUG FIX: Update UI
        showNotification("Settings saved (Mock Mode).", 'success');
        return;
    }
    if (!userId) return;

    const dataToSave = { name: newName, role: newRole };

    try {
        await db.collection(getCollectionPath('users')).doc(userId).set(dataToSave, { merge: true });
        
        // *** BUG FIX ***
        // Manually update all UI elements, including the form fields
        updateUIWithUserData(dataToSave); 
        
        showNotification("Settings saved successfully!", 'success');
        console.log("Settings saved!");
    } catch (error) {
        console.error("Error saving settings:", error);
        showNotification("Failed to save settings.", 'error');
    }
}

/**
 * Loads all users into the "Users" table.
 */
function loadAllUsers() {
    if (isMockMode) {
        mockLoadAllUsers();
        return;
    }

    db.collection(getCollectionPath('users')).onSnapshot(snapshot => {
        const usersTable = document.getElementById('users-table-body');
        let html = '';
        let expertsOnline = 0;
        
        if (snapshot.empty) {
            usersTable.innerHTML = '<tr><td colspan="5">No users found.</td></tr>';
            return;
        }

        snapshot.docs.forEach(doc => {
            const user = doc.data();
            const id = doc.id;
            if (id === userId) return; 

            if (user.role === 'expert' && user.status === 'online') {
                expertsOnline++;
            }

            html += `
                <tr>
                    <td>
                        <div class="user-cell">
                            <span class="user-cell-avatar">${getInitials(user.name)}</span>
                            <span>${user.name || 'N/A'}</span>
                        </div>
                    </td>
                    <td>${getRoleBadge(user.role)}</td>
                    <td>${getStatusBadge(user.status)}</td>
                    <td>${id}</td>
                    <td class="actions-cell">
                        <button class="action-btn primary" data-user-id="${id}" data-user-name="${user.name}" data-user-role="${user.role}" title="Start Chat">
                            <i class="fa-solid fa-paper-plane"></i>
                        </button>
                    </td>
                </tr>
            `;
        });
        usersTable.innerHTML = html;
        
        // Update dashboard stats
        const statsTotalUsers = document.getElementById('stats-total-users');
        statsTotalUsers.textContent = snapshot.size;
        const statsExpertsOnline = document.getElementById('stats-experts-online');
        statsExpertsOnline.textContent = expertsOnline;

        // Add click listeners to the chat buttons
        usersTable.querySelectorAll('.action-btn.primary').forEach(button => {
            button.addEventListener('click', (e) => {
                const recipientId = e.currentTarget.getAttribute('data-user-id');
                const recipientName = e.currentTarget.getAttribute('data-user-name');
                const recipientRole = e.currentTarget.getAttribute('data-user-role');
                
                // Switch to messages page and open chat
                showPage('page-messages');
                openChat(recipientId, recipientName, recipientRole);
            });
        });

    }, error => {
        console.error("Error loading users:", error);
        showNotification("Failed to load user list.", 'error');
        document.getElementById('users-table-body').innerHTML = '<tr><td colspan="5">Error loading users.</td></tr>';
    });
}

// --- 4. Firestore Data Logic (Chat / "Messages" Page) ---

/**
 * Opens the chat page for a specific user.
 * @param {string} recipientId
 * @param {string} recipientName
 * @param {string} recipientRole
 */
function openChat(recipientId, recipientName, recipientRole) {
    currentChatRecipientId = recipientId;

    // Show chat window header and form
    document.getElementById('chat-window-header').style.display = 'flex';
    document.getElementById('chat-send-form').style.display = 'flex';
    
    // Update chat header
    document.getElementById('chat-header-avatar').textContent = getInitials(recipientName);
    document.getElementById('chat-with-name').textContent = recipientName;
    document.getElementById('chat-with-role').textContent = recipientRole;
    
    // Highlight active conversation in the list
    document.querySelectorAll('.conversation-item').forEach(item => {
        item.classList.toggle('active', item.getAttribute('data-user-id') === recipientId);
    });
    
    // Load messages for this chat
    if (!isMockMode) {
        loadMessages(recipientId);
    } else {
        mockLoadMessages(recipientId);
    }
}

/**
 * Loads and displays messages for the current chat.
 * @param {string} recipientId
 */
function loadMessages(recipientId) {
    if (currentChatUnsubscribe) {
        currentChatUnsubscribe();
    }
    
    const chatMessagesContainer = document.getElementById('chat-messages-container');
    chatMessagesContainer.innerHTML = '<div class="message-bubble-placeholder">Loading messages...</div>';

    const chatId = getChatId(userId, recipientId);
    const messagesRef = db.collection(getCollectionPath('chats')).doc(chatId).collection('messages').orderBy('timestamp');

    currentChatUnsubscribe = messagesRef.onSnapshot(snapshot => {
        if (snapshot.empty) {
            chatMessagesContainer.innerHTML = '<div class="message-bubble-placeholder">No messages yet. Say hello!</div>';
            return;
        }

        let messagesHtml = '';
        snapshot.docs.forEach(doc => {
            const msg = doc.data();
            const messageId = doc.id;
            messagesHtml += createMessageBubble(msg, messageId);
        });
        
        chatMessagesContainer.innerHTML = messagesHtml;
        chatMessagesContainer.scrollTop = chatMessagesContainer.scrollHeight;

    }, error => {
        console.error("Error loading messages:", error);
        showNotification("Failed to load messages for this chat.", 'error');
        chatMessagesContainer.innerHTML = '<div class="message-bubble-placeholder">Error loading messages.</div>';
    });
}

/**
 * Creates the HTML for a single message bubble.
 * @param {object} msg - The message object from Firestore.
 * @param {string} messageId - The Firestore document ID for the message.
 * @returns {string} HTML string for the message.
 */
function createMessageBubble(msg, messageId) {
    const type = msg.senderId === userId ? 'sent' : 'received';
    // Sanitize timestamp
    let time = '...';
    if (msg.timestamp) {
        if (msg.timestamp.toDate) {
            time = msg.timestamp.toDate().toLocaleTimeString();
        } else if (msg.timestamp.toLocaleTimeString) {
            time = msg.timestamp.toLocaleTimeString();
        } else {
            try {
                time = new Date(msg.timestamp).toLocaleTimeString();
            } catch(e) {}
        }
    }
    
    const userRole = document.getElementById('current-user-role').textContent;
    const deleteButton = userRole === 'admin' ? 
        `<button class="action-btn danger delete-message-btn" data-message-id="${messageId}" title="Delete Message">
            <i class="fa-solid fa-times"></i>
        </button>` : '';

    return `
        <div class="message-bubble ${type}">
            ${deleteButton}
            ${msg.text}
            <span class="message-meta">${time}</span>
        </div>
    `;
}

/**
 * Handles sending a new chat message.
 * @param {Event} event - The form submit event.
 */
async function handleSendMessage(event) {
    event.preventDefault();
    
    const messageInput = document.getElementById('chat-message-input');
    const messageText = messageInput.value.trim();
    if (messageText === '') return;

    if (isMockMode) {
        console.log("Mock Send:", messageText);
        const container = document.getElementById('chat-messages-container');
        if(container.querySelector('.message-bubble-placeholder')) {
            container.innerHTML = ''; // Clear placeholder
        }
        const bubble = createMessageBubble({
            text: messageText,
            senderId: 'mock-user-id', // 'sent'
            timestamp: new Date()
        }, 'mock-message-id');
        container.innerHTML += bubble;
        container.scrollTop = container.scrollHeight;
        messageInput.value = '';
        return;
    }
    
    if (!currentChatRecipientId || !userId) return;
    
    const chatId = getChatId(userId, currentChatRecipientId);
    const messagesRef = db.collection(getCollectionPath('chats')).doc(chatId).collection('messages');
    const chatHistoryRef = db.collection(getCollectionPath('chats')).doc(chatId);

    const messageData = {
        text: messageText,
        senderId: userId,
        recipientId: currentChatRecipientId,
        timestamp: firebase.firestore.FieldValue.serverTimestamp()
    };

    try {
        await messagesRef.add(messageData);
        await chatHistoryRef.set({
            participants: [userId, currentChatRecipientId],
            lastMessage: messageText,
            lastUpdated: firebase.firestore.FieldValue.serverTimestamp()
        }, { merge: true });
        messageInput.value = '';
    } catch (error) {
        console.error("Error sending message:", error);
        showNotification("Failed to send message.", 'error');
    }
}

/**
 * Loads the user's chat history into the conversation list.
 */
function loadChatHistory() {
    if (isMockMode) {
        mockLoadChatHistory();
        return;
    }
    if (!userId) return;

    const conversationList = document.getElementById('conversation-list-container');
    const statsTotalChats = document.getElementById('stats-total-chats');

    db.collection(getCollectionPath('chats'))
      .where('participants', 'array-contains', userId)
      .onSnapshot(async (snapshot) => {
        
        if (snapshot.empty) {
            conversationList.innerHTML = '<div class="conversation-list-empty">No chat history.</div>';
            statsTotalChats.textContent = '0';
            return;
        }

        statsTotalChats.textContent = snapshot.size;
        
        const promises = snapshot.docs.map(async (doc) => {
            const chat = doc.data();
            const recipientId = chat.participants.find(p => p !== userId);
            
            if (!recipientId) return null; 
            
            const userDoc = await db.collection(getCollectionPath('users')).doc(recipientId).get();
            const recipientData = userDoc.exists ? userDoc.data() : { name: 'Unknown User', role: 'user' };

            return `
                <div class="conversation-item" data-user-id="${recipientId}" data-user-name="${recipientData.name}" data-user-role="${recipientData.role}">
                    <span class="user-cell-avatar">${getInitials(recipientData.name)}</span>
                    <div class="conversation-info">
                        <span class="user-name">${recipientData.name}</span>
                        <span class="last-message">${chat.lastMessage || '...'}</span>
                    </div>
                </div>
            `;
        });

        const results = await Promise.all(promises);
        conversationList.innerHTML = results.filter(Boolean).join('');

        // Add click listeners to open the chat
        conversationList.querySelectorAll('.conversation-item').forEach(button => {
            button.addEventListener('click', (e) => {
                const item = e.currentTarget;
                const recipientId = item.getAttribute('data-user-id');
                const recipientName = item.getAttribute('data-user-name');
                const recipientRole = item.getAttribute('data-user-role');
                openChat(recipientId, recipientName, recipientRole);
            });
        });

    }, error => {
        console.error("Error loading chat history:", error);
        showNotification("Failed to load chat history.", 'error');
        conversationList.innerHTML = '<div class="conversation-list-empty">Error loading history.</div>';
    });
}


// --- 5. Firestore Data Logic (Moderation / "Reports") ---

/**
 * Loads all chat conversations for moderation (Admins only).
 */
function loadModerationData() {
    if (isMockMode) {
        mockLoadModerationData();
        return;
    }
    
    const userRole = document.getElementById('current-user-role').textContent;
    if (userRole !== 'admin') {
        document.getElementById('reports-table-body').innerHTML = '<tr><td colspan="4">You do not have permission to view this page.</td></tr>';
        return;
    }

    const moderationTable = document.getElementById('reports-table-body');

    db.collection(getCollectionPath('chats')).onSnapshot(async (snapshot) => {
        if (snapshot.empty) {
            moderationTable.innerHTML = '<tr><td colspan="4">No conversations found.</td></tr>';
            return;
        }

        const promises = snapshot.docs.map(async (doc) => {
            const chat = doc.data();
            const chatId = doc.id;
            const participantIds = chat.participants || [];
            
            const participantNames = await Promise.all(
                participantIds.map(async (id) => {
                    const userDoc = await db.collection(getCollectionPath('users')).doc(id).get();
                    return userDoc.exists ? userDoc.data().name : 'Unknown';
                })
            );

            return `
                <tr>
                    <td>${chatId}</td>
                    <td>${participantNames.join(', ')}</td>
                    <td>${chat.lastMessage || '...'}</td>
                    <td class="actions-cell">
                        <button class="action-btn primary" data-chat-id="${chatId}" title="View Conversation">
                            <i class="fa-solid fa-eye"></i>
                        </button>
                    </td>
                </tr>
            `;
        });

        const results = await Promise.all(promises);
        moderationTable.innerHTML = results.join('');

        // Add click listeners
        moderationTable.querySelectorAll('.action-btn.primary').forEach(button => {
            button.addEventListener('click', (e) => {
                const chatId = e.currentTarget.getAttribute('data-chat-id');
                
                db.collection(getCollectionPath('chats')).doc(chatId).get().then(doc => {
                    const participants = doc.data().participants;
                    const recipientId = participants.find(p => p !== userId); 
                    if(recipientId) {
                         db.collection(getCollectionPath('users')).doc(recipientId).get().then(userDoc => {
                            const data = userDoc.data();
                            showPage('page-messages'); // Switch to messages page
                            openChat(recipientId, data.name, data.role);
                         });
                    }
                });
            });
        });
    }, error => {
        console.error("Error loading moderation data:", error);
        moderationTable.innerHTML = '<tr><td colspan="4">Error loading conversations.</td></tr>';
    });
}

/**
 * *** BUG FIX ***
 * New function to handle delete clicks via event delegation.
 * @param {Event} event - The click event.
 */
function handleDeleteMessageClick(event) {
    const deleteButton = event.target.closest('.delete-message-btn');
    if (deleteButton) {
        const messageId = deleteButton.getAttribute('data-message-id');
        showConfirmation('Are you sure you want to delete this message?', () => {
            deleteMessage(messageId);
        });
    }
}


/**
 * Deletes a specific message from Firestore (Admin action).
 * @param {string} messageId - The ID of the message to delete.
 */
async function deleteMessage(messageId) {
    if (isMockMode) {
        console.log("Mock Delete Message:", messageId);
        showNotification("Message deleted (Mock Mode).", 'success');
        // Manually remove from UI
        const msgElement = document.querySelector(`[data-message-id="${messageId}"]`);
        if(msgElement) msgElement.closest('.message-bubble').remove();
        return;
    }
    if (!currentChatRecipientId) return;

    const chatId = getChatId(userId, currentChatRecipientId);
    const messageRef = db.collection(getCollectionPath('chats')).doc(chatId).collection('messages').doc(messageId);

    try {
        await messageRef.delete();
        console.log("Message deleted:", messageId);
        // UI updates automatically via onSnapshot
    } catch (error) {
        console.error("Error deleting message:", error);
        showNotification("Failed to delete message.", 'error');
    }
}


// --- 6. NEW PAGE LOGIC (Stories, Initiatives, Stats) ---

function loadDashboardStats() {
    if (isMockMode) {
        document.getElementById('stats-total-users').textContent = '148';
        document.getElementById('stats-total-chats').textContent = '42';
        document.getElementById('stats-experts-online').textContent = '8';
        document.getElementById('stats-total-stories').textContent = '212';
        document.getElementById('stats-total-initiatives').textContent = '19';
        document.getElementById('stats-total-reports').textContent = '3';
    } else {
        db.collection(getCollectionPath('stories')).onSnapshot(snap => {
            document.getElementById('stats-total-stories').textContent = snap.size;
        });
        db.collection(getCollectionPath('initiatives')).onSnapshot(snap => {
            document.getElementById('stats-total-initiatives').textContent = snap.size;
        });
         // This is a placeholder; a real app would query for "pending" reports
        db.collection(getCollectionPath('reports')).onSnapshot(snap => {
            document.getElementById('stats-total-reports').textContent = snap.size;
        });
    }
}

function loadStories() {
    const tableBody = document.getElementById('stories-table-body');
    if (isMockMode) {
        tableBody.innerHTML = `
            <tr>
                <td>A New Beginning</td>
                <td>Alice Green</td>
                <td><span class="role-badge" style="background-color: rgba(46, 204, 113, 0.2); color: var(--primary-color);">Positive</span></td>
                <td><span class="status-badge active">Approved</span></td>
                <td class="actions-cell">
                    <button class="action-btn primary"><i class="fa-solid fa-eye"></i></button>
                    <button class="action-btn danger"><i class="fa-solid fa-trash"></i></button>
                </td>
            </tr>
            <tr>
                <td>My Journey</td>
                <td>Bob White</td>
                <td><span class="role-badge user">Neutral</span></td>
                <td><span class="status-badge active">Approved</span></td>
                <td class="actions-cell">
                    <button class="action-btn primary"><i class="fa-solid fa-eye"></i></button>
                    <button class="action-btn danger"><i class="fa-solid fa-trash"></i></button>
                </td>
            </tr>
             <tr>
                <td>Community Garden</td>
                <td>Charlie Black</td>
                <td><span class="role-badge" style="background-color: rgba(231, 76, 60, 0.2); color: var(--rouge-alerte);">Reported</span></td>
                <td><span class="status-badge inactive">Pending</span></td>
                <td class="actions-cell">
                    <button class="action-btn primary"><i class="fa-solid fa-eye"></i></button>
                    <button class="action-btn danger"><i class="fa-solid fa-trash"></i></button>
                </td>
            </tr>
        `;
        return;
    }
    tableBody.innerHTML = '<tr><td colspan="5">No stories found.</td></tr>';
}

function loadInitiatives() {
    const tableBody = document.getElementById('initiatives-table-body');
    if (isMockMode) {
        tableBody.innerHTML = `
            <tr>
                <td>Park Cleanup</td>
                <td>Ecology</td>
                <td>David Lee</td>
                <td><span class="status-badge active">Approved</span></td>
                <td class="actions-cell">
                    <button class="action-btn success"><i class="fa-solid fa-check"></i></button>
                    <button class="action-btn danger"><i class="fa-solid fa-trash"></i></button>
                </td>
            </tr>
            <tr>
                <td>Soup Kitchen</td>
                <td>Solidarity</td>
                <td>Eva Moon</td>
                <td><span class="status-badge inactive">Pending</span></td>
                <td class="actions-cell">
                    <button class="action-btn success"><i class="fa-solid fa-check"></i></button>
                    <button class="action-btn danger"><i class="fa-solid fa-trash"></i></button>
                </td>
            </tr>
        `;
        return;
    }
    tableBody.innerHTML = '<tr><td colspan="5">No initiatives found.</td></tr>';
}

function loadStatistics() {
    // This will draw mock charts regardless of mode for this demo
    drawMockCharts();
}

// --- 7. MOCK MODE Functions ---

function initializeAppWithMockData() {
    userId = 'mock-user-id';
    document.getElementById('current-user-id-display').textContent = userId;
    updateUIWithUserData({ name: "Mock Admin", role: "admin" });

    loadDashboardStats();
    mockLoadAllUsers();
    mockLoadChatHistory();
    mockLoadModerationData();
    loadStories(); 
    loadInitiatives(); 
    loadStatistics(); 
}

function mockLoadAllUsers() {
    const usersTable = document.getElementById('users-table-body');
    usersTable.innerHTML = `
        <tr>
            <td><div class="user-cell"><span class="user-cell-avatar">AG</span><span>Alice Green</span></div></td>
            <td>${getRoleBadge('expert')}</td>
            <td>${getStatusBadge('online')}</td>
            <td>user-alice-123</td>
            <td class="actions-cell">
                <button class="action-btn primary" data-user-id="user-alice-123" data-user-name="Alice Green" data-user-role="expert" title="Start Chat">
                    <i class="fa-solid fa-paper-plane"></i>
                </button>
            </td>
        </tr>
        <tr>
            <td><div class="user-cell"><span class="user-cell-avatar">BW</span><span>Bob White</span></div></td>
            <td>${getRoleBadge('student')}</td>
            <td>${getStatusBadge('inactive')}</td>
            <td>user-bob-456</td>
            <td class="actions-cell">
                <button class="action-btn primary" data-user-id="user-bob-456" data-user-name="Bob White" data-user-role="student" title="Start Chat">
                    <i class="fa-solid fa-paper-plane"></i>
                </button>
            </td>
        </tr>
    `;
    usersTable.querySelectorAll('.action-btn.primary').forEach(button => {
        button.addEventListener('click', (e) => {
            const recipientId = e.currentTarget.getAttribute('data-user-id');
            const recipientName = e.currentTarget.getAttribute('data-user-name');
            const recipientRole = e.currentTarget.getAttribute('data-user-role');
            showPage('page-messages');
            openChat(recipientId, recipientName, recipientRole);
        });
    });
}

function mockLoadChatHistory() {
    const conversationList = document.getElementById('conversation-list-container');
    conversationList.innerHTML = `
        <div class="conversation-item" data-user-id="user-alice-123" data-user-name="Alice Green" data-user-role="expert">
            <span class="user-cell-avatar">AG</span>
            <div class="conversation-info">
                <span class="user-name">Alice Green</span>
                <span class="last-message">That's a great idea!</span>
            </div>
        </div>
        <div class="conversation-item" data-user-id="user-bob-456" data-user-name="Bob White" data-user-role="student">
            <span class="user-cell-avatar">BW</span>
            <div class="conversation-info">
                <span class="user-name">Bob White</span>
                <span class="last-message">OK, see you then.</span>
            </div>
        </div>
    `;
    conversationList.querySelectorAll('.conversation-item').forEach(button => {
        button.addEventListener('click', (e) => {
            const item = e.currentTarget;
            const recipientId = item.getAttribute('data-user-id');
            const recipientName = item.getAttribute('data-user-name');
            const recipientRole = item.getAttribute('data-user-role');
            openChat(recipientId, recipientName, recipientRole);
        });
    });
}

function mockLoadModerationData() {
    const moderationTable = document.getElementById('reports-table-body');
    moderationTable.innerHTML = `
        <tr>
            <td>chat_mock-user-id_user-bob-456</td>
            <td>Mock Admin, Bob White</td>
            <td>This is an inappropriate message.</td>
            <td class="actions-cell">
                <button class="action-btn primary" data-chat-id="chat_mock-user-id_user-bob-456" title="View Conversation">
                    <i class="fa-solid fa-eye"></i>
                </button>
            </td>
        </tr>
    `;
    moderationTable.querySelectorAll('.action-btn.primary').forEach(button => {
        button.addEventListener('click', () => {
            showNotification("Viewing specific chats is disabled in Mock Mode. Opening a mock chat.", 'success');
            showPage('page-messages');
            openChat('user-bob-456', 'Bob White', 'student');
        });
    });
}

function mockLoadMessages(recipientId) {
    const chatMessagesContainer = document.getElementById('chat-messages-container');
    chatMessagesContainer.innerHTML = `
        <div class="message-bubble received">
            Hey, how is the project going?
            <span class="message-meta">10:30 AM</span>
        </div>
        <div class="message-bubble sent">
             <button class="action-btn danger delete-message-btn" data-message-id="mock-msg-1" title="Delete Message">
                <i class="fa-solid fa-times"></i>
            </button>
            It's going well! Just working on the mock data.
            <span class="message-meta">10:31 AM</span>
        </div>
        <div class="message-bubble received">
            Nice! Let me know if you need help.
            <span class="message-meta">10:32 AM</span>
        </div>
    `;
    chatMessagesContainer.scrollTop = chatMessagesContainer.scrollHeight;
}


/**
 * Draws simple mock charts on the canvas elements.
 */
function drawMockCharts() {
    // --- Chart 1: User Growth ---
    let userChartCtx = document.getElementById('userGrowthChart').getContext('2d');
    if (userChartCtx) {
        const data = [10, 30, 25, 50, 45, 70];
        const labels = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'];
        drawBarChart(userChartCtx, data, labels, 'User Growth', '#5DADE2');
    }
    userChartCtx = document.getElementById('userGrowthChart-main').getContext('2d');
    if (userChartCtx) {
        const data = [10, 30, 25, 50, 45, 70];
        const labels = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'];
        drawBarChart(userChartCtx, data, labels, 'User Growth', '#5DADE2');
    }


    // --- Chart 2: Emotional Trends ---
    let emotionChartCtx = document.getElementById('emotionTrendChart').getContext('2d');
    if (emotionChartCtx) {
        const data = [120, 50, 30];
        const labels = ['Positive', 'Neutral', 'Negative'];
        const colors = ['#7BD389', '#5DADE2', '#F4A261'];
        drawDoughnutChart(emotionChartCtx, data, labels, 'Story Emotions', colors);
    }
    emotionChartCtx = document.getElementById('emotionTrendChart-main').getContext('2d');
     if (emotionChartCtx) {
        const data = [120, 50, 30];
        const labels = ['Positive', 'Neutral', 'Negative'];
        const colors = ['#7BD389', '#5DADE2', '#F4A261'];
        drawDoughnutChart(emotionChartCtx, data, labels, 'Story Emotions', colors);
    }
}

/**
 * Helper function to draw a simple bar chart.
 */
function drawBarChart(ctx, data, labels, title, color) {
    const canvas = ctx.canvas;
    const width = canvas.width;
    const height = canvas.height;
    const padding = 40;
    const barWidth = (width - 2 * padding) / data.length * 0.8;
    const barSpacing = (width - 2 * padding) / data.length * 0.2;
    const maxValue = Math.max(...data);
    const scale = (height - 2 * padding) / maxValue;

    ctx.clearRect(0, 0, width, height);
    
    ctx.fillStyle = '#333';
    ctx.font = 'bold 16px Poppins';
    ctx.textAlign = 'center';
    ctx.fillText(title, width / 2, padding / 2 + 10);

    data.forEach((value, index) => {
        const x = padding + index * (barWidth + barSpacing) + barSpacing / 2;
        const y = height - padding - value * scale;
        const barHeight = value * scale;
        ctx.fillStyle = color;
        ctx.fillRect(x, y, barWidth, barHeight);
        ctx.fillStyle = '#666';
        ctx.font = '12px Open Sans';
        ctx.textAlign = 'center';
        ctx.fillText(labels[index], x + barWidth / 2, height - padding + 20);
    });
    
    ctx.fillStyle = '#666';
    ctx.font = '12px Open Sans';
    ctx.textAlign = 'right';
    ctx.fillText('0', padding - 10, height - padding);
    ctx.fillText(maxValue.toString(), padding - 10, padding);
}

/**
* Helper function to draw a simple doughnut chart.
*/
function drawDoughnutChart(ctx, data, labels, title, colors) {
    const canvas = ctx.canvas;
    const width = canvas.width;
    const height = canvas.height;
    const radius = Math.min(width, height) / 2 - 40;
    const centerX = width / 2;
    const centerY = height / 2;
    const total = data.reduce((a, b) => a + b, 0);
    let startAngle = -0.5 * Math.PI; 

    ctx.clearRect(0, 0, width, height);
    
    ctx.fillStyle = '#333';
    ctx.font = 'bold 16px Poppins';
    ctx.textAlign = 'center';
    ctx.fillText(title, centerX, 25);

    data.forEach((value, index) => {
        const sliceAngle = (value / total) * 2 * Math.PI;
        const endAngle = startAngle + sliceAngle;
        ctx.beginPath();
        ctx.moveTo(centerX, centerY);
        ctx.arc(centerX, centerY, radius, startAngle, endAngle);
        ctx.closePath();
        ctx.fillStyle = colors[index % colors.length];
        ctx.fill();
        startAngle = endAngle;
    });

    ctx.beginPath();
    ctx.arc(centerX, centerY, radius * 0.5, 0, 2 * Math.PI);
    ctx.fillStyle = 'var(--blanc-pur)';
    ctx.fill();
    
    let legendY = height - (labels.length * 20);
    labels.forEach((label, index) => {
        ctx.fillStyle = colors[index % colors.length];
        ctx.fillRect(40, legendY + index * 20 - 10, 10, 10);
        ctx.fillStyle = '#333';
        ctx.font = '12px Open Sans';
        ctx.textAlign = 'left';
        ctx.fillText(`${label}: ${data[index]}`, 60, legendY + index * 20);
    });
}


// --- 8. Utility Functions ---

/**
 * Displays a notification message in a modal.
 * @param {string} message - The message to display.
 * @param {string} type - 'success' or 'error'.
 */
function showNotification(message, type = 'success') {
    const modal = document.getElementById('notification-modal');
    const modalHeader = document.getElementById('notification-modal-header');
    const modalMessage = document.getElementById('notification-modal-message');
    const modalIcon = modalHeader.querySelector('i');

    modalMessage.textContent = message;

    if (type === 'error') {
        modalHeader.classList.add('error');
        modalIcon.className = 'fa-solid fa-exclamation-triangle';
    } else {
        modalHeader.classList.remove('error');
        modalIcon.className = 'fa-solid fa-check-circle';
    }
    
    modal.style.display = 'flex';
}

/**
 * A simple modal to replace window.confirm()
 * @param {string} message - The confirmation question.
 * @param {function} onConfirm - Callback function to run if confirmed.
 */
function showConfirmation(message, onConfirm) {
    const modal = document.getElementById('notification-modal');
    const modalHeader = document.getElementById('notification-modal-header');
    const modalMessage = document.getElementById('notification-modal-message');
    const modalIcon = modalHeader.querySelector('i');
    
    // Set up as confirmation
    modalHeader.classList.add('error'); // Use red for confirmation
    modalIcon.className = 'fa-solid fa-question-circle';
    
    const originalContent = modalMessage.innerHTML;
    modalMessage.innerHTML = `
        <p>${message}</p>
        <div style="text-align: right; margin-top: 20px;">
            <button class="btn btn-secondary" id="confirm-cancel">Cancel</button>
            <button class="btn btn-danger" id="confirm-ok" style="margin-left: 10px;">Confirm</button>
        </div>
    `;
    
    modal.style.display = 'flex';

    document.getElementById('confirm-ok').onclick = () => {
        onConfirm();
        modal.style.display = 'none';
        modalMessage.innerHTML = originalContent; // Restore
        modalHeader.classList.remove('error'); // Restore
    };
    document.getElementById('confirm-cancel').onclick = () => {
        modal.style.display = 'none';
        modalMessage.innerHTML = originalContent; // Restore
        modalHeader.classList.remove('error'); // Restore
    };
}


/**
 * Generates a consistent chat ID for two users.
 * @param {string} uid1 - First user ID.
 * @param {string} uid2 - Second user ID.
 *s * @returns {string} A sorted, combined chat ID.
 */
function getChatId(uid1, uid2) {
    return [uid1, uid2].sort().join('_');
}

/**
 * Gets the path for a public Firestore collection.
 * @param {string} collectionName - The name of the collection.
 * @returns {string} The full Firestore path.
 */
function getCollectionPath(collectionName) {
    return `artifacts/${APP_ID}/public/data/${collectionName}`;
}

/**
 * Creates a role badge HTML string.
 * @param {string} role - The user's role.
 * @returns {string} HTML string for the badge.
 */
function getRoleBadge(role) {
    role = (role || 'user').toLowerCase();
    let className = 'user'; // default
    if (role === 'admin') className = 'admin';
    if (role === 'expert') className = 'expert';
    
    return `<span class="role-badge ${className}">${role}</span>`;
}

/**
 * Creates a status badge HTML string.
 * @param {string} status - The user's status.
 * @returns {string} HTML string for the badge.
 */
function getStatusBadge(status) {
    status = (status || 'inactive').toLowerCase();
    let className = 'inactive';
    if (status === 'active' || status === 'online') className = 'active';

    return `<span class="status-badge ${className}">${status}</span>`;
}

/**
 * Gets the initials from a name.
 * @param {string} name - The user's full name.
 * @returns {string} The initials (e.g., "JD").
 */
function getInitials(name) {
    if (!name) return '?';
    const parts = name.trim().split(' ');
    if (parts.length > 1) {
        return (parts[0][0] + parts[parts.length - 1][0]).toUpperCase();
    }
    return name.substring(0, 2).toUpperCase();
}
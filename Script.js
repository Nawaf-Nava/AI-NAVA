/**
 * PROJECT: NAVA AI v9.5 - MULTIMODAL_CORE (FINAL_SYNC)
 * ENGINEER: NAWAF_ROOT (Cybersecurity Specialist)
 * STATUS: CORE_OPTIMIZED | EXTERNAL_SCRIPT_SOLO | FULL_SYNC
 */

/* --- [1] إعدادات النظام الموحدة (Unified System Config) --- */
const CONFIG = {
    API_URL: "api.php", 
    HISTORY_URL: "get_all_sessions.php", // المسار الموحد لجلب السجلات
    MESSAGES_URL: "get_session_messages.php", // جديد: لجلب محتوى المحادثة
    IMAGE_ENGINE_API: "https://nawafcsgir-cyberflux.hf.space/generate-image",
    MAX_HISTORY: 25,
    STORAGE_KEY: 'nava_active_node_id',
    SYSTEM_IDENTITY: "NAVA_ULTRA_v9.5",
    RENDER_DELAY: 30, // تحسين لتقليل استهلاك المعالج (CPU) مع الحفاظ على سلاسة الكتابة
    USER_AVATAR_PATH: "images/default-avatar.png",
    BOT_AVATAR_NORMAL: "images/icons/nava-normal.png",
    BOT_AVATAR_DEEP: "images/icons/nava-deep.png",
    VOICE_ENABLED: true,
    VOICE_ENGINE: "Edge-TTS",
};

/* --- [2] مصفوفة عناصر الواجهة المحدثة (DOM Matrix) --- */
const DOM = {
    chat: document.getElementById('chat-container'),
    input: document.getElementById('user-input'),
    welcomeHero: document.getElementById('welcome-hero'),
    mainActionBtn: document.getElementById('main-action-btn'),
    actionIcon: document.getElementById('action-icon'),
    attachBtn: document.getElementById('attach-toggle'),
    fileInput: document.getElementById('input-files'), // تصحيح المرجع ليتوافق مع index.php
    newChatBtn: document.getElementById('new-chat-btn'),
    saveBtn: document.getElementById('save-chat-btn'),
    historyList: document.getElementById('history-list'),
    userAvatar: document.querySelector('.profile-trigger img'),
    filePreview: document.getElementById('file-preview-container'),
    visualPreview: document.getElementById('visual-preview'),
    fileIcon: document.getElementById('file-icon'),
    fileNameDisplay: document.getElementById('file-name-display'),
    removeFileBtn: document.getElementById('remove-file'),
    sidebar: document.getElementById('sidebar'),
    sidebarToggle: document.getElementById('history-toggle-btn'),
    sidebarClose: document.getElementById('close-sidebar'),
    profileTrigger: document.getElementById('profile-trigger-btn'),
    profileModal: document.getElementById('profile-modal'),
    overlay: document.getElementById('overlay'),
    deepModeModal: document.getElementById('deep-mode-modal'), // New: Deep Mode Confirmation Modal
    confirmDeepMode: document.getElementById('confirm-deep-mode'),
    cancelDeepMode: document.getElementById('cancel-deep-mode'),
    deepModeToggle: document.getElementById('deep-mode-toggle'),
    liveModeBtn: document.getElementById('live-mode-btn'),
    voiceInputBar: document.getElementById('voice-input-bar'),
    modalClose: document.getElementById('close-modal'),

    refresh() {
        this.chat = document.getElementById('chat-container');
        this.input = document.getElementById('user-input');
        this.welcomeHero = document.getElementById('welcome-hero');
        this.mainActionBtn = document.getElementById('main-action-btn');
        this.actionIcon = document.getElementById('action-icon');
        this.attachBtn = document.getElementById('attach-toggle');
        this.fileInput = document.getElementById('input-files');
        this.newChatBtn = document.getElementById('new-chat-btn');
        this.saveBtn = document.getElementById('save-chat-btn');
        this.historyList = document.getElementById('history-list');
        this.userAvatar = document.querySelector('.profile-trigger img');
        this.filePreview = document.getElementById('file-preview-container');
        this.visualPreview = document.getElementById('visual-preview');
        this.fileIcon = document.getElementById('file-icon');
        this.fileNameDisplay = document.getElementById('file-name-display');
        this.removeFileBtn = document.getElementById('remove-file');
        this.sidebar = document.getElementById('sidebar');
        this.sidebarToggle = document.getElementById('history-toggle-btn');
        this.sidebarClose = document.getElementById('close-sidebar');
        this.profileTrigger = document.getElementById('profile-trigger-btn');
        this.profileModal = document.getElementById('profile-modal');
        this.overlay = document.getElementById('overlay');
        this.deepModeModal = document.getElementById('deep-mode-modal'); // New: Deep Mode Confirmation Modal
        this.confirmDeepMode = document.getElementById('confirm-deep-mode');
        this.cancelDeepMode = document.getElementById('cancel-deep-mode');
        this.deepModeToggle = document.getElementById('deep-mode-toggle');
        this.liveModeBtn = document.getElementById('live-mode-btn');
        this.voiceInputBar = document.getElementById('voice-input-bar');
        this.modalClose = document.getElementById('close-modal');
    },

    validate() {
        this.refresh();
        Object.keys(this).forEach(key => {
            if (!this[key] && typeof this[key] !== 'function' && key !== 'sidebarToggle' && key !== 'profileTrigger') {
                console.warn(`[NAVA_DOM_WARN]: Element #${key} is missing in the current layout.`);
            }
        });
    }
};

/* --- [3] حالة النظام الديناميكية (Dynamic State Management) --- */
let chatState = {
    history: [],               
    isProcessing: false,       
    activeRequestObj: null,    
    isTyping: false,           
    isRecording: false,        
    sessionId: null, // تأمين بدء جلسة جديدة دائماً عند الإقلاع (تجاهل المخزن سابقاً)
    allSessions: [],           
    pendingFile: null,         
    pendingFileType: null,     
    wasVoiceInput: false,      // تتبع ما إذا كان الإدخال صوتياً
    
    syncStorage() {
        try {
            if (this.sessionId && this.sessionId !== 'GUEST_SESSION') sessionStorage.setItem(CONFIG.STORAGE_KEY, this.sessionId);
        } catch (e) {
            console.warn("[NAVA_SYSTEM]: Storage sync failed (Guest/Private Mode).");
        }
    }
};

chatState.syncStorage();

/* --- [4] أدوات النظام الاحترافية (Nava Utility Engine) --- */
const Utils = {
    scrollBottom: () => {
        if (DOM.chat) {
            DOM.chat.scrollTo({ top: DOM.chat.scrollHeight, behavior: 'smooth' });
        }
    },

    isImageRequest: (text) => {
        const triggers = ['ارسم', 'صورة', 'تخيل', 'draw', 'generate image', 'imagine', 'visualize', 'تخيلي', 'صمم', 'ابتكر صورة'];
        return triggers.some(t => text.toLowerCase().includes(t));
    },

    escapeHtml: (unsafe) => {
        if (!unsafe) return "";
        return unsafe
            .replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;")
            .replace(/"/g, "&quot;").replace(/'/g, "&#039;");
    },

    handleBrokenImage: (img) => {
        const fallback = document.createElement('div');
        fallback.className = 'image-error-fallback';
        fallback.innerHTML = `<i class="fa-solid fa-image-slash"></i> <span>DATA_NODE_UNAVAILABLE</span>`;
        img.parentNode.replaceChild(fallback, img);
        console.warn("[NAVA_VISUAL]: Image data expired or unreachable.");
    },

   formatRichContent: (text) => {
        if (!text) return "";
        let tempText = text;

        // [تصحيح هندسي]: معالجة وسم بيانات المباراة باستخدام مجموعة التقاط (Capturing Group)
        const matchRegex = /\[MATCH_DATA\]([\s\S]*?)\[\/MATCH_DATA\]/g;
        tempText = tempText.replace(matchRegex, (match, jsonStr) => {
            try {
                const d = JSON.parse(jsonStr);
                return `
                <div class="match-card" dir="ltr">
                    <div class="match-team">
                        <img src="${d.l1}" onerror="this.src='images/ooo.png'">
                        <span>${d.t1}</span>
                    </div>
                    <div class="match-score-info">
                        <div class="match-score-val">${d.s}</div>
                        <div class="match-status">${d.st}</div>
                    </div>
                    <div class="match-team">
                        <img src="${d.l2}" onerror="this.src='images/ooo.png'">
                        <span>${d.t2}</span>
                    </div>
                </div>`;
            } catch (e) { return ""; }
        });

        const codeBlockCount = (tempText.match(/```/g) || []).length;
        if (codeBlockCount % 2 !== 0 && !tempText.endsWith('```')) {
            tempText += "\n\n```"; 
        }

        if (typeof marked !== 'undefined') {
            marked.use({ breaks: true, gfm: true });
            let htmlContent = marked.parse(tempText);
            const container = document.createElement('div');
            container.innerHTML = htmlContent;

            container.querySelectorAll('a').forEach(link => {
                link.setAttribute('target', '_blank');
                link.classList.add('cyber-link');
            });

            // --- التحديث الهندسي المصحح لمربع الأكواد (Gemini Style - No Syntax Errors) ---
            container.querySelectorAll('pre').forEach(pre => {
                const codeElement = pre.querySelector('code');
                const rawContent = codeElement ? codeElement.innerText : pre.innerText;
                const langClass = codeElement?.className || '';
                const langMatch = langClass.match(/language-(\w+)/);
                const language = langMatch ? langMatch[1].toLowerCase() : 'code';
                
                // [تحسين]: دعم تشغيل كافة لغات العمليات السيبرانية
                const isRunnable = ['python', 'html', 'javascript', 'bash', 'sh', 'shell', 'linux', 'php'].includes(language);
                const runBtnHtml = isRunnable ? `
                    <button class="gemini-run-btn" onclick="Utils.runCode(this)" title="تشغيل الكود">
                        <i class="fa-solid fa-play"></i> <span>RUN</span>
                    </button>` : '';

                const copyText = "نسخ";
                const saveText = "تحميل";

                pre.className = "gemini-code-container";
                pre.innerHTML = `
                    <div class="gemini-code-header" dir="ltr">
                        <span class="gemini-lang-badge">${language}</span>
                        <div class="code-actions">
                            ${runBtnHtml}
                            <button class="gemini-copy-btn" onclick="Utils.copyCode(this)" title="نسخ الكود">
                                <i class="fa-regular fa-copy"></i> <span class="copy-label">${copyText}</span>
                            </button>
                            <button class="gemini-download-btn" onclick="Utils.downloadCode(this)" title="حفظ كملف">
                                <i class="fa-solid fa-download"></i> <span class="save-label">${saveText}</span>
                            </button>
                        </div>
                    </div>
                    <div class="gemini-code-body" dir="ltr">
                        <code class="hljs language-${language}">${Utils.escapeHtml(rawContent.trim())}</code>
                    </div>
                `;
                pre.style.padding = "0"; 
                pre.style.background = "transparent";
            });
            return container.innerHTML;
        }
        return Utils.escapeHtml(tempText);
    },

    copyCode: (btn) => {
        const pre = btn.closest('pre');
        const code = pre.querySelector('code').innerText;
        navigator.clipboard.writeText(code).then(() => {
            const span = btn.querySelector('span');
            const icon = btn.querySelector('i');
            span.innerText = "تم النسخ!";
            icon.className = "fa-solid fa-check";
            btn.style.borderColor = "var(--neon-green)";
            setTimeout(() => {
                span.innerText = "نسخ";
                icon.className = "fa-regular fa-copy";
                btn.style.borderColor = "";
            }, 2000);
        });
    },

    downloadCode: (btn) => {
        const pre = btn.closest('pre');
        const code = pre.querySelector('code').innerText;
        const lang = pre.querySelector('.gemini-lang-badge').innerText.toLowerCase();
        const extMap = { 'javascript':'js', 'python':'py', 'php':'php', 'html':'html', 'css':'css', 'cpp':'cpp', 'sql':'sql', 'json':'json' };
        const ext = extMap[lang] || 'txt';
        const blob = new Blob([code], { type: 'text/plain' });
        const url = URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = `nava_source_${Date.now()}.${ext}`;
        a.click();
        URL.revokeObjectURL(url);
        
        const icon = btn.querySelector('i');
        const originalClass = icon.className;
        icon.className = "fa-solid fa-check fa-beat";
        setTimeout(() => icon.className = originalClass, 2000);
    },

    runCode: async (btn) => {
        const pre = btn.closest('pre');
        const code = pre.querySelector('code').innerText;
        const lang = pre.querySelector('.gemini-lang-badge').innerText.toLowerCase();
        let output = "";
        
        const icon = btn.querySelector('i');
        icon.className = "fa-solid fa-spinner fa-spin";

        if (lang === 'html') {
            const win = window.open('', '_blank');
            win.document.write(code);
            win.document.close();
            icon.className = "fa-solid fa-play";
            return;
        }

        try {
            let endpoint = 'run_python.php';
            if (['bash', 'sh', 'shell', 'linux'].includes(lang)) endpoint = 'run_shell.php';
            
            if (lang === 'javascript') {
                // تم إزالة eval() لأسباب أمنية.
                output = "JS_EVAL_DISABLED: JavaScript execution in the browser is disabled for security reasons. Please run the code in your browser's console.";
                Utils.showNotification("تم تعطيل تشغيل JavaScript مباشرةً للأمان.", "warning");
                // يمكنك عرض الكود في نافذة جديدة ليقوم المستخدم بنسخه
                // const win = window.open('', '_blank');
                // win.document.write('<pre>' + Utils.escapeHtml(code) + '</pre>');
                // win.document.close();
            } else {
                const res = await fetch(endpoint, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ 
                        code: code,
                        session_id: chatState.sessionId 
                    })
                });
                const data = await res.json();
                output = data.output || data.error || "No response from kernel.";
            }
            // [الميزة المطلوبة]: إرسال النتيجة إلى NAVA للتحليل التلقائي
            if (output) {
                Utils.showNotification("جاري تحليل النتائج عبر النواة...", "success");
                handleSend(`[SYSTEM_EXECUTION_RESULT]:\nLanguage: ${lang}\nCommand Output:\n${output}\n\nAnalyze this output and suggest the next logical step.`);
            }

        } catch (e) {
            Utils.showNotification("فشل الاتصال بمحرك التشغيل", "error");
        } finally {
            icon.className = "fa-solid fa-play";
        }
    },

    initCodeHighlight: () => {
        if (typeof hljs !== 'undefined') {
            document.querySelectorAll('pre code').forEach((block) => {
                hljs.highlightElement(block);
            });
        }
    },

    generateSessionId: () => {
        const id = 'NAVA_' + Date.now() + '_' + Math.random().toString(36).substr(2, 5).toUpperCase();
        try {
            sessionStorage.setItem(CONFIG.STORAGE_KEY, id);
            chatState.sessionId = id;
            return id;
        } catch (e) {
            chatState.sessionId = id;
            return id;
        }
    },

    getQueryParams: () => {
        const params = {};
        window.location.search.substring(1).split("&").forEach(param => {
            const parts = param.split("=");
            if (parts[0]) {
                params[decodeURIComponent(parts[0])] = decodeURIComponent(parts[1] || "");
            }
        });
        return params;
    },

    showNotification: (msg, type = 'info') => {
        const zone = document.getElementById('notification-zone');
        if (!zone) return;
        
        const toast = document.createElement('div');
        toast.className = `cyber-toast ${type}`;
        const icon = type === 'error' ? 'fa-triangle-exclamation' : 'fa-circle-info';
        const color = type === 'error' ? 'var(--neon-red)' : 'var(--cyber-cyan)';
        
        toast.innerHTML = `<i class="fa-solid ${icon}" style="color: ${color}"></i> <span>${msg}</span>`;
        zone.appendChild(toast);
        
        setTimeout(() => {
            toast.style.opacity = '0';
            setTimeout(() => toast.remove(), 300);
        }, 4000);
    },

    createNewChat: () => {
        if (chatState.isProcessing) return;
        
        // توليد معرف جلسة جديد دون إعادة تحميل الصفحة للحفاظ على الوضع الحالي
        Utils.generateSessionId();
        chatState.history = [];
        
        // إعادة ضبط واجهة المستخدم
        DOM.refresh();
        if (DOM.chat) {
            DOM.chat.innerHTML = "";
            DOM.chat.style.display = 'none';
        }
        if (DOM.welcomeHero) DOM.welcomeHero.style.display = 'flex';
        setupWelcomeInterface();
        if (DOM.input) {
            DOM.input.value = "";
            DOM.input.style.height = 'auto';
        }
        updateActionButton();
        console.log("[SYSTEM]: New session initialized. Mode preserved.");
    }
};

/* --- [4.5] وظائف الحركة والتحريك (UI Animations) --- */
function toggleIconAnimation(container, isAnimating) {
    if (!container) return;
    
    if (isAnimating) {
        container.classList.add('thinking-mode');
    } else {
        container.classList.remove('thinking-mode');
    }

    const botAvatarFrame = container.querySelector('.bot-avatar-frame');
    
    if (isAnimating) {
        if (botAvatarFrame) botAvatarFrame.classList.add('thinking-avatar');
    } else {
        if (botAvatarFrame) botAvatarFrame.classList.remove('thinking-avatar');
    }
}

/* --- [5] محرك الصوت والتعرف عليه (Sensory Engine) --- */
let speechRecognition;
const VoiceEngine = {
    speak: (text, onStart, onEnd) => {
        // Check if voice is enabled in config
        if (!CONFIG.VOICE_ENABLED) {
            console.warn("[VOICE_ENGINE]: Voice output is disabled in CONFIG.");
            return;
        }

        // Ensure SpeechSynthesis API is available
        if (!window.speechSynthesis) {
            console.warn("[VOICE_ENGINE]: SpeechSynthesis API not supported in this browser.");
            return;
        }

        // تنظيف الرموز التنسيقية والإيموجيات لضمان عدم نطق أسمائها
        const cleanText = text
            .replace(/[*#_~`>]/g, '') 
            .replace(/https?:\/\/\S+/g, 'رابط خارجي')
            .replace(/\p{Extended_Pictographic}/gu, ''); // حذف الإيموجيات برمجياً
            
        window.speechSynthesis.cancel();
        document.querySelectorAll('.tts-btn').forEach(b => b.classList.remove('playing'));

        // تقسيم النص إلى جمل كاملة لضمان القراءة بأسلوب سردي (كتاب) بدلاً من كلمات مقطعة
        const sentences = cleanText.match(/[^.!?؟\n]+[.!?؟\n]*/g) || [cleanText];
        let currentIdx = 0;
        const isDeep = isDeepMode;
        
        const voices = window.speechSynthesis.getVoices();

        // اختيار الصوت: أنثوي للوضع العادي، وذكوري/تقني للوضع العميق
        const arabicFemaleVoice = voices.find(v => v.lang.startsWith('ar') && (v.name.includes('Zariyah') || v.name.includes('Amina') || v.name.includes('Neural'))) || 
                                  voices.find(v => v.lang.startsWith('ar') && (v.name.includes('Female') || v.name.includes('Laila'))) ||
                                  voices.find(v => v.lang.startsWith('ar'));
        
        // تحسين اختيار صوت الهكر الفخم (رجل)
        const arabicMaleVoice = voices.find(v => v.lang.startsWith('ar') && (v.name.includes('Hamed') || v.name.includes('Maged') || v.name.includes('Male'))) || 
                                voices.find(v => v.lang.startsWith('ar'));

        const selectedVoice = isDeep ? arabicMaleVoice : arabicFemaleVoice;

        const speakNext = () => {
            // تحديث النص في واجهة LIVE أثناء التحدث
            const statusText = document.getElementById('live-status-text');
            if (document.body.classList.contains('live-active') && statusText) {
                statusText.innerText = "NAVA_SPEAKING...";
                statusText.style.color = "var(--neon-green)";
                document.body.classList.add('live-speaking');
            }

            if (currentIdx >= sentences.length) {
                if (document.body.classList.contains('live-active') && statusText) {
                    statusText.innerText = "SYSTEM_LISTENING...";
                    statusText.style.color = "var(--cyber-cyan)";
                    document.body.classList.remove('live-speaking');
                    setTimeout(() => startVoiceRecognition(), 500); // العودة للاستماع تلقائياً
                }
                if (onEnd) onEnd();
                return;
            }

            const sentence = sentences[currentIdx].trim();
            if (!sentence) { currentIdx++; return speakNext(); }

            const utterance = new SpeechSynthesisUtterance(sentence);
            utterance.voice = selectedVoice;
            utterance.lang = 'ar-SA'; // تثبيت اللغة العربية كأساس لضمان القراءة السلسة
            utterance.rate = isDeep ? 1.4 : 1.55; // رفع السرعة لضمان الطلاقة والتدفق السريع
            utterance.pitch = isDeep ? 0.4 : 1.0; // نبرة أكثر فخامة في الوضع العميق

            if (currentIdx === 0 && onStart) utterance.onstart = onStart;
            
            utterance.onend = () => {
                currentIdx++;
                speakNext();
            };

            utterance.onerror = (event) => {
                console.error("[VOICE_ENGINE_ERROR]:", event);
                if (onEnd) onEnd();
            };

            window.speechSynthesis.speak(utterance);
        };

        speakNext();
    },

    stop: () => {
        window.speechSynthesis.cancel();
        document.body.classList.remove('live-speaking');
    }
};

// دالة التحكم في وضع LIVE
function toggleLiveMode() {
    const isActive = document.body.classList.toggle('live-active');
    const statusBtn = document.getElementById('live-mode-btn');
    
    if (isActive) {
        statusBtn.classList.add('active-live');
        Utils.showNotification("تم تفعيل وضع المحادثة المباشرة", "info");
        startVoiceRecognition();
    } else {
        statusBtn.classList.remove('active-live');
        VoiceEngine.stop();
        if (chatState.isRecording) speechRecognition.stop();
    }
};

function initVoiceEngine() {
    const SpeechRecognition = window.SpeechRecognition || window.webkitSpeechRecognition;
    
    if (!SpeechRecognition) {
        console.warn("[NAVA_SYSTEM]: Speech Recognition not supported in this browser.");
        return;
    }

    speechRecognition = new SpeechRecognition();
    speechRecognition.lang = 'ar-SA';
    speechRecognition.interimResults = false;
    speechRecognition.continuous = false;

    speechRecognition.onstart = () => {
        chatState.isRecording = true;
        chatState.wasVoiceInput = true; // تفعيل خيار الرد الصوتي التلقائي عند بدء التسجيل
        if (DOM.actionIcon) DOM.actionIcon.className = "fa-solid fa-microphone-lines fa-beat";
        if (DOM.mainActionBtn) DOM.mainActionBtn.style.background = "var(--neon-red)";
        
        // تحسين الواجهة عند بدء التسجيل
        if (DOM.input) DOM.input.style.display = 'none';
        if (DOM.voiceInputBar) DOM.voiceInputBar.classList.add('active');
    };

    speechRecognition.onresult = (event) => {
        const transcript = event.results[0][0].transcript;
        DOM.refresh();
        if (DOM.input) {
            DOM.input.value = transcript;
            updateActionButton();
            handleSend();
        }
    };

    speechRecognition.onend = () => {
        chatState.isRecording = false;
        updateActionButton();
        if (DOM.mainActionBtn) DOM.mainActionBtn.style.background = "";
        // إعادة الواجهة لحالتها الأصلية
        if (DOM.input) DOM.input.style.display = 'block';
        if (DOM.voiceInputBar) DOM.voiceInputBar.classList.remove('active');
    };

    speechRecognition.onerror = (event) => {
        chatState.isRecording = false;
        console.error("[VOICE_ERROR]:", event.error);
        updateActionButton();
        if (DOM.mainActionBtn) DOM.mainActionBtn.style.background = "";
        // إعادة الواجهة لحالتها الأصلية
        if (DOM.input) DOM.input.style.display = 'block';
        if (DOM.voiceInputBar) DOM.voiceInputBar.classList.remove('active');
    };
}

async function startVoiceRecognition() {
    if (!speechRecognition) initVoiceEngine();

    if (!speechRecognition) {
        Utils.showNotification("[ SYSTEM_ERROR ]: محرك الصوت غير مدعوم أو غير مستجيب.", "error");
        return;
    }

    try {
        // البدء المباشر للمحرك؛ المتصفح سيتكفل بإظهار نافذة الإذن تلقائياً إذا لم تكن موجودة
        speechRecognition.start();
    } catch (e) {
        if (e.name === 'InvalidStateError') {
            speechRecognition.stop();
        } else {
            console.error("[VOICE_START_ERROR]:", e);
        }
    }
}

async function fileToBase64(file) {
    return new Promise((resolve, reject) => {
        const reader = new FileReader();
        reader.readAsDataURL(file);
        reader.onload = () => resolve(reader.result.split(',')[1]);
        reader.onerror = error => reject(error);
    });
}

/* --- [6] مزامنة استرجاع السجلات (Historical Synchronization) --- */
async function loadHistoryFromServer() {
    if (!chatState.sessionId || chatState.sessionId === 'GUEST_SESSION') return;

    try {
        const response = await fetch(`${CONFIG.MESSAGES_URL}?session_id=${encodeURIComponent(chatState.sessionId)}`);
        if (!response.ok) throw new Error("Network response was not ok");
        
        const data = await response.json();
        
        if (data.status === 'success' && Array.isArray(data.history)) {
            DOM.refresh();
            if (DOM.chat) DOM.chat.innerHTML = ""; // تنظيف اللودر

            chatState.history = data.history;
            if (chatState.history.length > 0) {
                chatState.history.forEach(msg => {
                    const role = (msg.role === 'model' || msg.role === 'assistant') ? 'bot' : 'user';
                    renderMessage(role, msg.content, false); 
                });
                setTimeout(() => Utils.scrollBottom(), 100);
            } else {
                // في حال كانت العقدة فارغة، نتأكد من بقاء شاشة الترحيب نشطة
                if (DOM.welcomeHero) DOM.welcomeHero.style.display = 'flex';
                if (DOM.chat) DOM.chat.style.display = 'none';
            }
            console.log(`[NAVA_SYNC]: ${chatState.history.length} segments loaded.`);
        }
    } catch (error) {
        if (DOM.chat) DOM.chat.innerHTML = `<div class="error-node">⚠️ [RESTORATION_FAILED]: تعذر الاتصال بالخزنة.</div>`;
        console.warn("[NAVA_SYNC_PENDING]:", error);
    }
}

function updateActionButton() {
    if (!DOM.mainActionBtn || !DOM.actionIcon) return;
    const hasInput = (DOM.input && DOM.input.value.trim() !== "") || chatState.pendingFile;

    if (chatState.isProcessing || chatState.isTyping) {
        DOM.mainActionBtn.classList.add('stop-mode');
        DOM.actionIcon.className = "fa-solid fa-circle-stop"; 
    } else {
        DOM.mainActionBtn.classList.remove('stop-mode');
        DOM.actionIcon.className = hasInput ? "fa-solid fa-arrow-up" : "fa-solid fa-microphone";
    }
}

/* --- [7] المعالجة المركزية وإرسال البيانات (Multimodal Core Logic) --- */
async function handleSend(customText = null) {
    DOM.refresh(); 
    if (chatState.isProcessing) return;

    const query = customText !== null ? customText.trim() : (DOM.input ? DOM.input.value.trim() : "");
    
    // إذا قام المستخدم بالكتابة يدوياً، نلغي الرد الصوتي التلقائي
    // نتحقق مما إذا كان الإرسال تم عبر الضغط المباشر وليس عبر نتيجة محرك الصوت
    if (!chatState.isRecording && customText === null) chatState.wasVoiceInput = false;

    if (!query && !chatState.pendingFile) return;

    if (DOM.welcomeHero) DOM.welcomeHero.style.display = 'none';
    if (DOM.chat) DOM.chat.style.display = 'block';

    let safeQuery = Utils.escapeHtml(query);
    let displayMsg = safeQuery.replace(/\n/g, '<br>');
    
    if (chatState.pendingFile) {
        if (chatState.pendingFile.type.startsWith('image/')) {
            const imgSrc = DOM.visualPreview.src;
            displayMsg = `<div class="user-attachment-container"><img src="${imgSrc}" class="user-attached-image" onerror="Utils.handleBrokenImage(this)"></div>${displayMsg}`;
        } else {
            displayMsg = `<div class="file-attachment-tag"><i class="fa-solid fa-file-shield"></i> ${Utils.escapeHtml(chatState.pendingFile.name)}</div>${displayMsg}`;
        }
    }
    
    renderMessage("user", displayMsg);
    
    if (DOM.input) {
        DOM.input.value = "";
        DOM.input.style.height = 'auto';
    }
    
    if (DOM.filePreview) DOM.filePreview.style.display = 'none';
    
    chatState.isProcessing = true;
    updateActionButton();
    
    const botMsgContainer = createBotThinkingContainer();
    // تفعيل الدوران فوراً
    toggleIconAnimation(botMsgContainer, true);

    try {
        // نرسل الطلب دائماً للنواة لتحدد هي ما إذا كان المستخدم يريد صورة أم نصاً
        await processMultimodalRequest(query, botMsgContainer);
    } catch (error) {
        if (error.name !== 'AbortError') {
            const contentDiv = botMsgContainer.querySelector('.msg-content');
            contentDiv.innerHTML = `<div class="error-node">⚠️ **[CRITICAL_ERROR]:** انقطع الاتصال بالنواة.</div>`;
        }
        toggleIconAnimation(botMsgContainer, false);
    } finally {
        chatState.isProcessing = false;
        chatState.pendingFile = null;
        if (DOM.fileInput) DOM.fileInput.value = '';
        updateActionButton();
    }
}

function createBotThinkingContainer() {
    const msgDiv = document.createElement('div');
    msgDiv.className = `message bot-message alert-sync-dir`; 
    const botIcon = (typeof isDeepMode !== 'undefined' && isDeepMode) ? CONFIG.BOT_AVATAR_DEEP : CONFIG.BOT_AVATAR_NORMAL;
    
    msgDiv.innerHTML = `
        <div class="msg-wrapper bot-layout-sync">
            <div class="msg-header">
                <div class="bot-identity" style="display: flex; align-items: center; gap: 10px; flex-direction: row;">
                    <div class="bot-avatar-frame">
                        <img src="${botIcon}" alt="NAVA" class="bot-mini-logo" onerror="this.src='images/icons/nava-normal.png'">
                    </div>
                </div>
                <button class="tts-btn" title="قراءة الرد" style="display: none;">
                    <i class="fa-solid fa-volume-high"></i>
                </button>
            </div>
            <div class="msg-content bot-text">
                <span class="typing-dots">جاري التفكير..</span>
            </div>
        </div>`;
        
    DOM.chat.appendChild(msgDiv);
    Utils.scrollBottom();
    return msgDiv;
}

async function processMultimodalRequest(query, botMsgContainer) {
    const controller = new AbortController();
    chatState.activeRequestObj = controller;

    let payload = { 
        message: query,
        session_id: chatState.sessionId,
        deep_mode: (typeof isDeepMode !== 'undefined' ? isDeepMode : false)
    };

    if (chatState.pendingFile) {
        payload.file_data = await fileToBase64(chatState.pendingFile);
        payload.mime_type = chatState.pendingFile.type;
    }

    try {
        const response = await fetch(CONFIG.API_URL, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(payload),
            signal: controller.signal
        });

        if (!response.ok) throw new Error("API_OFFLINE");
        const data = await response.json();
        
        const reply = data.reply || "";
        const searchResults = data.search_results || [];
        
        // فحص ما إذا كان الذكاء الاصطناعي قد قرر توليد صورة
        if (reply.startsWith("[GENERATE_IMAGE]")) {
            const imagePrompt = reply.replace("[GENERATE_IMAGE]", "").trim();
            await generateVisual(imagePrompt, botMsgContainer);
        } else {
            // الرد الصوتي الفوري في حال كان الرد نصياً
            if (chatState.wasVoiceInput && CONFIG.VOICE_ENABLED && reply) {
                const ttsBtn = botMsgContainer.querySelector('.tts-btn');
                VoiceEngine.speak(reply, 
                    () => ttsBtn?.classList.add('playing'), 
                    () => ttsBtn?.classList.remove('playing')
                );
                chatState.wasVoiceInput = false; 
            }
            await fillBotMessage(botMsgContainer, reply || "ERROR: NO_RESPONSE_FROM_CORE");
            if (searchResults && searchResults.length > 0) {
                renderSearchResults(botMsgContainer, searchResults);
            }
        }
    } catch (err) {
        if (err.name !== 'AbortError') throw err;
    }
}

/**
 * عرض نتائج البحث المباشرة أسفل الرد بتصميم سيبراني
 */
function renderSearchResults(container, results) {
    if (!results || results.length === 0) return;
    
    const contentDiv = container.querySelector('.msg-content');
    const wrapper = document.createElement('div');
    wrapper.className = 'search-results-wrapper';
    
    let html = `
        <div class="search-header">
            <i class="fa-solid fa-magnifying-glass"></i>
            <span>المصادر المسترجعة</span>
        </div>
    `;
    
    results.forEach(res => {
        html += `
            <a href="${res.link}" target="_blank" class="search-item">
                <div class="search-meta">
                    <span class="search-credibility"><i class="fa-solid fa-shield-check"></i> ${res.credibility}</span>
                </div>
                <span class="search-title">${Utils.escapeHtml(res.title)}</span>
                <span class="search-snippet">${Utils.escapeHtml(res.snippet)}</span>
                <span class="search-link">${Utils.escapeHtml(res.link)}</span>
            </a>
        `;
    });
    
    wrapper.innerHTML = html;
    contentDiv.appendChild(wrapper);
    Utils.scrollBottom();
}

async function fillBotMessage(msgDiv, text) {
    const contentDiv = msgDiv.querySelector('.msg-content');
    chatState.isTyping = true;
    toggleIconAnimation(msgDiv, true);
    updateActionButton(); // تحديث الزر ليصبح "إيقاف" عند بدء الكتابة

    // [تحسين توفير الطاقة]: إذا كان الوضع نشطاً، اظهر النص فوراً دون أنميشن
    if (document.body.classList.contains('power-saving-mode')) {
        contentDiv.innerHTML = Utils.formatRichContent(text);
        if (typeof Utils.initCodeHighlight === 'function') Utils.initCodeHighlight();
        toggleIconAnimation(msgDiv, false);
        chatState.isTyping = false;
        updateActionButton();
        return Promise.resolve();
    }

    let i = 0;
    contentDiv.innerHTML = ""; 

    return new Promise((resolve) => {
        const interval = setInterval(() => {
            // التحقق من طلب الإيقاف الفوري
            if (!chatState.isTyping) {
                clearInterval(interval);
                toggleIconAnimation(msgDiv, false);
                contentDiv.innerHTML += ' <span style="color:var(--neon-red); font-size:0.7rem; font-family:var(--font-code);">[اوقف الرد</span>';
                resolve();
                return;
            }

            // التحقق من نهاية النص
            if (i >= text.length) {
                clearInterval(interval);
                toggleIconAnimation(msgDiv, false); 
                contentDiv.innerHTML = Utils.formatRichContent(text);
                contentDiv.classList.add('typing-done'); 
                
                if(typeof Utils.initCodeHighlight === 'function') Utils.initCodeHighlight();
                
                // تفعيل زر القراءة بعد انتهاء الكتابة
                const ttsBtn = msgDiv.querySelector('.tts-btn');
                if (ttsBtn && CONFIG.VOICE_ENABLED) {
                    ttsBtn.style.display = 'inline-flex';
                    ttsBtn.onclick = () => {
                        if (ttsBtn.classList.contains('playing')) {
                            VoiceEngine.stop();
                            ttsBtn.classList.remove('playing');
                        } else {
                            VoiceEngine.speak(text, () => ttsBtn.classList.add('playing'), () => ttsBtn.classList.remove('playing'));
                        }
                    };
                }

                chatState.isTyping = false;
                updateActionButton(); // إعادة الزر لحالته الطبيعية عند انتهاء الكتابة
                resolve();
                return;
            }
            
            i += 5; // زيادة سرعة ظهور الحروف (بناء سريع)
            contentDiv.innerHTML = Utils.formatRichContent(text.slice(0, i));
        }, CONFIG.RENDER_DELAY);
    });
}

async function generateVisual(prompt, botMsgContainer) {
    const contentDiv = botMsgContainer.querySelector('.msg-content');
    contentDiv.innerHTML = `
        <div class="gen-loader">
            <i class="fa-solid fa-wand-magic-sparkles fa-spin"></i> 
            <span>جاري تخيل المشهد عبر FLUX.1...</span>
        </div>`;
    
    const controller = new AbortController();
    chatState.activeRequestObj = controller;

    try {
        const response = await fetch(CONFIG.IMAGE_ENGINE_API, {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify({ 
                prompt: prompt, 
                session_id: chatState.sessionId || "GUEST",
                deep_mode: (typeof isDeepMode !== 'undefined' ? isDeepMode : false)
            }),
            signal: controller.signal
        });

        if (!response.ok) {
            toggleIconAnimation(botMsgContainer, false);
            throw new Error("NODE_BUSY");
        }
        const contentType = response.headers.get("content-type");
        let imgUrl;

        // إذا كانت الاستجابة JSON (رابط صورة)
        if (contentType && contentType.includes("application/json")) {
            const data = await response.json();
            imgUrl = data.url || data.image || (data.images && data.images[0]);
        } 
        // إذا كانت الاستجابة ملف صورة خام (Blob)
        else {
            const blob = await response.blob();
            if (blob.size > 0 && blob.type.startsWith('image/')) {
                imgUrl = URL.createObjectURL(blob);
            }
        }

        if (!imgUrl) {
            toggleIconAnimation(botMsgContainer, false);
            throw new Error("EMPTY_IMAGE_RESPONSE");
        }
        
        toggleIconAnimation(botMsgContainer, false);
        contentDiv.innerHTML = `
            <div class="generated-img-container">
                <div class="cyber-image-wrapper">
                    <img src="${imgUrl}" alt="NAVA Generated Image" class="fluid-img" onload="Utils.scrollBottom()" onerror="Utils.handleBrokenImage(this)">
                    <div class="image-overlay-actions">
                        <button onclick="window.open('${imgUrl}', '_blank')" title="عرض بالحجم الكامل">
                            <i class="fa-solid fa-expand"></i>
                        </button>
                        <button onclick="Utils.copyToClipboard('${imgUrl}')" title="نسخ الرابط">
                            <i class="fa-solid fa-link"></i>
                        </button>
                    </div>
                </div>
                <div class="img-meta">
                    <div class="meta-left">
                        <i class="fa-solid fa-wand-magic-sparkles" style="color:var(--cyber-cyan)"></i>
                        <span>تم التصميم بواسطة FLUX.1</span>
                    </div>
                    <a href="${imgUrl}" download="NAVA_VISION_${Date.now()}.png" class="download-link">
                        <i class="fa-solid fa-download"></i> <span>حفظ</span>
                    </a>
                </div>
            </div>`;
        Utils.scrollBottom();
    } catch (e) {
        if (e.name === 'AbortError') return; // تجاهل الخطأ إذا تم الإيقاف بواسطة المستخدم
        
        contentDiv.innerHTML = `<div class='error-node'>❌ فشل محرك الصور: ${e.message}</div>`;
        toggleIconAnimation(botMsgContainer, false);
    }
}

function stopGeneration() {
    if (chatState.activeRequestObj) {
        chatState.activeRequestObj.abort();
        chatState.activeRequestObj = null;
    }
    chatState.isProcessing = false;
    chatState.isTyping = false;

    // تنظيف حالة "جاري الرد" من الواجهة فوراً
    const currentThinking = document.querySelector('.bot-message.thinking-mode') || 
                            document.querySelector('.bot-message:last-child');
    if (currentThinking) {
        currentThinking.classList.remove('thinking-mode');
        const content = currentThinking.querySelector('.msg-content');
        if (content && (content.innerText.includes('جاري تحليل') || content.querySelector('.typing-dots'))) {
            content.innerHTML = `<span style="color:var(--text-muted); font-size:0.8rem; font-family:var(--font-code);">[ SESSION_TERMINATED_BY_USER ]</span>`;
        }
    }

    // ضمان إيقاف جميع حركات التفكير في الواجهة
    const allThinkingAvatars = document.querySelectorAll('.bot-avatar-frame.thinking-avatar');
    allThinkingAvatars.forEach(el => el.classList.remove('thinking-avatar'));

    updateActionButton();
    console.log("[SYSTEM]: Core process terminated by user.");
}

/* --- [8] محرك واجهة بناء الرسائل المسترجعة (Rendering Engine) --- */
function renderMessage(role, text, animate = true) {
    const msgDiv = document.createElement('div');
    msgDiv.className = `message ${role}-message ${animate ? 'animate-in' : ''}`;
    const botIcon = (typeof isDeepMode !== 'undefined' && isDeepMode) ? CONFIG.BOT_AVATAR_DEEP : CONFIG.BOT_AVATAR_NORMAL;
    
    if (role === "user") {
        msgDiv.innerHTML = `
            <div class="msg-wrapper user-layout-sync">
                <div class="msg-content user-text">
                    ${text}
                </div>
            </div>`;
    } else {
        msgDiv.innerHTML = `
            <div class="msg-wrapper bot-layout-sync">
                <div class="msg-header">
                    <div class="bot-identity" style="display: flex; align-items: center; gap: 10px; flex-direction: row;">
                        <div class="bot-avatar-frame">
                            <img src="${botIcon}" alt="NAVA" class="bot-mini-logo" onerror="this.src='images/icons/nava-normal.png'">
                        </div>
                    </div>
                    <button class="tts-btn" title="قراءة الرد">
                        <i class="fa-solid fa-volume-high"></i>
                    </button>
                </div>
                <div class="msg-content bot-text">
                    ${Utils.formatRichContent(text)}
                </div>
            </div>`;
            
        const ttsBtn = msgDiv.querySelector('.tts-btn');
        if (ttsBtn && CONFIG.VOICE_ENABLED) {
            ttsBtn.onclick = () => {
                if (ttsBtn.classList.contains('playing')) {
                    VoiceEngine.stop(); // Stop any currently speaking utterance
                    ttsBtn.classList.remove('playing');
                } else {
                    VoiceEngine.speak(text, () => ttsBtn.classList.add('playing'), () => ttsBtn.classList.remove('playing'));
                }
            };
        } else if (ttsBtn) {
            ttsBtn.style.display = 'none'; // Hide button if voice is disabled
        }
    }
    
    if (DOM.chat) {
        DOM.chat.appendChild(msgDiv);
        // تفعيل التلوين البرمجي فوراً بعد إضافة الرسالة للواجهة
        if (typeof Utils.initCodeHighlight === 'function') Utils.initCodeHighlight();
        if (animate) Utils.scrollBottom();
    }
}

/* --- [9] نظام الإقلاع وربط النوافذ المنبثقة (System Bootstrap & UI Listeners) --- */
async function bootstrap() {
    console.log(`[SYSTEM]: Initializing ${CONFIG.SYSTEM_IDENTITY}...`);

    if (typeof DOM.validate === 'function') DOM.validate();
    if (!chatState.sessionId) Utils.generateSessionId();

    // [تصحيح هندسي]: تهيئة محرك الصوت داخل نظام الإقلاع لضمان جاهزية البيئة
    if (CONFIG.VOICE_ENABLED) {
        initVoiceEngine();
    }

    // إصلاح شريط التحميل وإخفاء شاشة الإقلاع
    const splashBar = document.getElementById('splash-bar');
    const splashScreen = document.getElementById('app-splash-screen');
    if (splashBar && splashScreen) {
        splashBar.style.width = '40%';
        setTimeout(() => {
            splashBar.style.width = '80%';
            splashBar.style.width = '100%';
            splashScreen.style.opacity = '0';
            setTimeout(() => splashScreen.style.visibility = 'hidden', 800);
        }, 1000);
    }

    // استرجاع المحادثة النشطة فور تحميل الصفحة
    if (chatState.sessionId) {
        loadHistoryFromServer();
    }

    // تخصيص واجهة الترحيب بناءً على حالة تسجيل الدخول
    setupWelcomeInterface();

    // إصلاح حلقة التحميل: ننتظر تحميل الصفحة بالكامل ثم نقرر جلب البيانات
    window.addEventListener('load', () => {
        console.log("[SYSTEM]: Page load complete.");
        if (typeof IS_LOGGED_IN !== 'undefined' && IS_LOGGED_IN) {
            loadHistoryToSidebar();
        }
    });

    // ربط مستمع الإدخال فوراً لضمان تحول الأيقونة للسهم عند الكتابة
    if (DOM.input) {
        DOM.input.oninput = function() {
            this.style.height = 'auto';
            this.style.height = (this.scrollHeight) + 'px';
            updateActionButton();
        };
    }

    if (DOM.mainActionBtn) {
        DOM.mainActionBtn.onclick = (e) => {
            e.preventDefault();
            DOM.refresh();
            if (chatState.isProcessing || chatState.isTyping) return stopGeneration();
            if (DOM.input.value.trim() === "" && !chatState.pendingFile) return startVoiceRecognition();
            handleSend();
        };
    }

    // ربط زر LIVE
    if (DOM.liveModeBtn) {
        DOM.liveModeBtn.onclick = (e) => { e.preventDefault(); window.location.href = 'live_mode.php'; };
    }

    DOM.input?.addEventListener('keydown', (e) => {
        if (e.key === 'Enter' && !e.shiftKey) {
            e.preventDefault();
            handleSend();
        }
    });

    if (DOM.newChatBtn) {
        DOM.newChatBtn.onclick = (e) => {
            e.preventDefault();
            Utils.createNewChat();
        };
    }

    // منطق قائمة المرفقات الجديدة
    const attachMenu = document.getElementById('attach-menu');
    const inputCamera = document.getElementById('input-camera');
    const inputPhotos = document.getElementById('input-photos');
    const inputFiles = document.getElementById('input-files');

    DOM.attachBtn?.addEventListener('click', (e) => {
        e.stopPropagation();
        attachMenu?.classList.toggle('active');
        DOM.attachBtn.querySelector('i').classList.toggle('fa-rotate-45');
    });

    document.getElementById('opt-camera')?.addEventListener('click', () => inputCamera.click());
    document.getElementById('opt-photos')?.addEventListener('click', () => inputPhotos.click());
    document.getElementById('opt-files')?.addEventListener('click', () => inputFiles.click());

    [inputCamera, inputPhotos, inputFiles].forEach(input => {
        input?.addEventListener('change', function() {
            handleFileSelect(this.files[0]);
            attachMenu?.classList.remove('active');
        });
    });

    function handleFileSelect(file) {
        if (file) {
            chatState.pendingFile = file;
            if (DOM.filePreview) DOM.filePreview.style.display = 'block';
            if (DOM.fileNameDisplay) DOM.fileNameDisplay.textContent = file.name;
            updateActionButton(); // تحديث الزر فور اختيار ملف

            if (file.type.startsWith('image/')) {
                const reader = new FileReader();
                reader.onload = (e) => {
                    if (DOM.visualPreview) {
                        DOM.visualPreview.src = e.target.result;
                        DOM.visualPreview.style.display = 'block';
                    }
                    if (DOM.fileIcon) DOM.fileIcon.style.display = 'none';
                };
                reader.readAsDataURL(file);
            } else {
                if (DOM.visualPreview) DOM.visualPreview.style.display = 'none';
                if (DOM.fileIcon) DOM.fileIcon.style.display = 'block';
            }
            console.log(`[VISION]: File "${file.name}" staged for upload.`);
        }
    }

    // إغلاق القائمة عند الضغط خارجها
    document.addEventListener('click', () => {
        attachMenu?.classList.remove('active');
        DOM.attachBtn?.querySelector('i').classList.remove('fa-rotate-45');
    });

    DOM.removeFileBtn?.addEventListener('click', (e) => {
        e.preventDefault();
        chatState.pendingFile = null;
        if (DOM.fileInput) DOM.fileInput.value = '';
        if (DOM.filePreview) DOM.filePreview.style.display = 'none';
        if (DOM.fileNameDisplay) DOM.fileNameDisplay.textContent = '';
        updateActionButton(); // إعادة الزر لوضع المايكروفون عند حذف الملف
    });

    // تبديل وفتح القائمة الجانبية (Sidebar) للأرشيف
    if (DOM.sidebarToggle) {
        DOM.sidebarToggle.addEventListener('click', (e) => {
            e.stopPropagation();
            if (DOM.sidebar) DOM.sidebar.classList.add('active');
        });
    }

    if (DOM.sidebarClose) {
        DOM.sidebarClose.addEventListener('click', () => {
            if (DOM.sidebar) DOM.sidebar.classList.remove('active');
        });
    }

    document.addEventListener('click', (e) => {
        if (DOM.sidebar && DOM.sidebar.classList.contains('active')) {
            if (!DOM.sidebar.contains(e.target) && DOM.sidebarToggle && !DOM.sidebarToggle.contains(e.target)) {
                DOM.sidebar.classList.remove('active');
            }
        }
    });

    // تبديل النافذة المنبثقة للملف الشخصي (Profile Modal)
    if (DOM.profileTrigger) {
        DOM.profileTrigger.addEventListener('click', () => {
            if (DOM.profileModal && DOM.overlay) {
                DOM.profileModal.classList.add('active');
                DOM.overlay.classList.add('active');
            }
        });
    }

    if (DOM.overlay) {
        DOM.overlay.addEventListener('click', () => {
            if (DOM.profileModal) DOM.profileModal.classList.remove('active');
            if (DOM.overlay) DOM.overlay.classList.remove('active');
        });
    }

    if (DOM.modalClose) {
        DOM.modalClose.addEventListener('click', () => {
            if (DOM.profileModal) DOM.profileModal.classList.remove('active');
            if (DOM.overlay) DOM.overlay.classList.remove('active');
        });
    }

    // حفظ وتدبيس الجلسات بداخل الخزنة السحابية
    if (DOM.saveBtn && typeof IS_LOGGED_IN !== 'undefined' && IS_LOGGED_IN) {
        DOM.saveBtn.onclick = async (e) => {
            e.preventDefault();
            const messagesElements = DOM.chat.querySelectorAll('.message');
            
            if (messagesElements.length === 0) {
                console.warn("[SYSTEM]: No data nodes found to archive.");
                return;
            }
            
            const chatTitle = prompt("أدخل عنوان الجلسة لتشفيرها وحفظها:");
            if (chatTitle) {
                const messageData = Array.from(messagesElements).map(msg => ({
                    role: msg.classList.contains('user-message') ? 'user' : 'assistant',
                    content: msg.querySelector('.msg-content')?.innerText.trim() || ""
                }));

                try {
                    const response = await fetch(`secure_vault_v8.php`, {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ 
                            session_id: chatState.sessionId, 
                            title: chatTitle, 
                            messages: messageData 
                        })
                    });
                    
                    if(response.ok) {
                        console.log("[SUCCESS]: Session encrypted and vaulted.");
                        await loadHistoryToSidebar();
                    }
                } catch(err) {
                    console.error("[CRITICAL]: Vault connection failed.", err);
                }
            }
        };
    }

    // --- [منطق الوضع العميق الموحد - Deep Mode Consolidated] ---
    if (DOM.deepModeToggle) {
        DOM.deepModeToggle.onclick = () => {
            if (!isDeepMode) {
                DOM.deepModeModal?.classList.add('active');
                DOM.overlay?.classList.add('active');
            } else {
                isDeepMode = false;
                DOM.deepModeToggle.classList.remove('active');
                document.body.classList.remove('deep-mode-active');
                VoiceEngine.speak("تم إغلاق النواة العميقة. العودة للوضع الآمن.");
                Utils.createNewChat();
            }
        };
    }

    if (DOM.confirmDeepMode) {
        DOM.confirmDeepMode.onclick = () => {
            isDeepMode = true;
            DOM.deepModeToggle?.classList.add('active');
            document.body.classList.add('deep-mode-active');
            VoiceEngine.speak("تم تفعيل بروتوكول النواة العميقة. الهوية المجهولة قيد التنفيذ.");
            DOM.deepModeModal?.classList.remove('active');
            DOM.overlay?.classList.remove('active');

            // تجديد المحادثة فوراً عند تفعيل الوضع العميق (Soft Reset)
            Utils.generateSessionId();
            chatState.history = [];
            if (DOM.input) {
                DOM.input.value = "";
                DOM.input.style.height = 'auto';
            }
            if (DOM.chat) {
                DOM.chat.innerHTML = "";
                DOM.chat.style.display = "none";
            }
            if (DOM.welcomeHero) DOM.welcomeHero.style.display = "flex";
                setupWelcomeInterface();
            updateActionButton();
        };
    }

    if (DOM.cancelDeepMode) {
        DOM.cancelDeepMode.onclick = () => {
            DOM.deepModeModal?.classList.remove('active');
            DOM.overlay?.classList.remove('active');
        };
    }

    // إغلاق نافذة تأكيد الوضع العميق
    const closeDeepModeModalBtn = document.getElementById('close-deep-mode-modal');
    if (closeDeepModeModalBtn) {
        closeDeepModeModalBtn.addEventListener('click', () => {
            if (DOM.deepModeModal) DOM.deepModeModal.classList.remove('active');
            if (DOM.overlay) DOM.overlay.classList.remove('active');
        });
    }

    console.log(`[SYSTEM]: Core ${CONFIG.SYSTEM_IDENTITY} is ONLINE.`);

    // تسجيل محرك التشغيل الأوفلاين (Service Worker)
    if ('serviceWorker' in navigator) {
        navigator.serviceWorker.register('sw.js')
            .then(() => console.log("[OFFLINE_ENGINE]: Activated Successfully."))
            .catch(err => console.error("[OFFLINE_ENGINE_ERROR]:", err));
    }
} 

function setupWelcomeInterface() {
    const heroLogo = document.querySelector('.hero-logo span');
    const suggestionGrid = document.getElementById('main-suggestion-grid');
    const heroSubtitle = document.querySelector('.hero-subtitle');
    const welcomeAvatarFrame = document.querySelector('.welcome-hero .bot-avatar-frame');
    
    if (!heroLogo || !suggestionGrid) return;

    // جلب حالة الوضع العميق
    const isDeep = isDeepMode;

    // [تعديل ستايل الزر]: تبديل الأيقونة بناءً على الوضع الحالي
    if (DOM.deepModeToggle) {
        const toggleIcon = DOM.deepModeToggle.querySelector('i');
        if (toggleIcon) {
            toggleIcon.className = isDeep ? "fa-solid fa-face-smile-beam" : "fa-solid fa-user-secret";
        }
        DOM.deepModeToggle.title = isDeep ? "العودة للوضع العادي" : "تفعيل الوضع العميق";
    }

    // تحديث أيقونة الترحيب لتكون صورة منفصلة (مثل المحادثة)
    const botIcon = isDeep ? CONFIG.BOT_AVATAR_DEEP : CONFIG.BOT_AVATAR_NORMAL;
    if (welcomeAvatarFrame) {
        welcomeAvatarFrame.innerHTML = `<img src="${botIcon}" alt="NAVA" style="width:100%; height:100%; object-fit:cover; border-radius:50%;" onerror="this.src='images/icons/nava-normal.png'">`;
        welcomeAvatarFrame.style.display = 'flex'; // إظهارها في شاشة الترحيب
        welcomeAvatarFrame.style.width = '80px';
        welcomeAvatarFrame.style.height = '80px';
    }
    
    const userName = (typeof CURRENT_USER_NAME !== 'undefined' && CURRENT_USER_NAME.trim() !== "" && CURRENT_USER_NAME !== "GUEST_USER") ? CURRENT_USER_NAME : "";

    // تخصيص النصوص والترحيب بناءً على الوضع المختار
    if (isDeep) {
        heroLogo.innerHTML = userName ? `ما هي أفكارك السيئة اليوم يا <span class="user-highlight">${userName}</span>؟` : "ما هي أفكارك السيئة اليوم؟";
        if (heroSubtitle) heroSubtitle.innerText = "بروتوكولات النواة العميقة نشطة. الأنظمة جاهزة للاستغلال المتقدم.";
    } else {
        heroLogo.innerHTML = userName ? `من أين نبدأ يا <span class="user-highlight">${userName}</span>؟` : "من أين نبدأ؟";
        if (heroSubtitle) heroSubtitle.innerText = "أنا NAVA، كيف يمكنني مساعدتك في أي شيء اليوم؟";
    }

    // قوائم الاقتراحات المخصصة لكل وضع (عادي / عميق)
    const normalSuggestions = [
        { icon: "fa-scroll", text: "أخبار اليوم", color: "#ff3366" }, // تم التغيير من fa-newspaper
        { icon: "fa-atom", text: "تجارب علمية", color: "#00ff88" }, // تم التغيير من fa-flask-vial
        { icon: "fa-lightbulb", text: "نصائح يومية", color: "#ffcc00" },
        { icon: "fa-terminal", text: "تعلم البرمجة", color: "#00f3ff" }, // تم التغيير من fa-laptop-code
        { icon: "fa-book-open", text: "تلخيص كتب", color: "#9d00ff" },
        { icon: "fa-image", text: "توليد صور", color: "#3366ff" }, // تم التغيير من fa-palette
        { icon: "fa-briefcase", text: "تطوير مهني", color: "#ff6600" },
        { icon: "fa-language", text: "ترجمة نصوص", color: "#00cccc" }
    ];

    const deepSuggestions = [
        { icon: "fa-virus-covid", text: "تطوير Malware", color: "#ff3366" },
        { icon: "fa-database", text: "حقن SQLi المتقدم", color: "#ff6600" },
        { icon: "fa-burst", text: "تنفيذ هجمات DDoS", color: "#ffcc00" },
        { icon: "fa-fish-fins", text: "إنشاء Phishing", color: "#ff3366" },
        { icon: "fa-biohazard", text: "Exploit Chain", color: "#9d00ff" },
        { icon: "fa-file-lock", text: "تشفير فدية", color: "#ff3366" },
        { icon: "fa-gears", text: "هندسة عكسية", color: "#00ff88" },
        { icon: "fa-mask", text: "Social Engineering", color: "#ff1493" }
    ];

    const suggestions = isDeep ? deepSuggestions : normalSuggestions;

    suggestionGrid.innerHTML = suggestions.map(s => `
        <div class="cyber-tool-card" onclick="handleQuickCommand('${s.text.replace(/'/g, "\\'")}')">
            <i class="fa-solid ${s.icon}" style="color: ${s.color} !important; filter: drop-shadow(0 0 5px ${s.color}66);"></i>
            <span>${s.text}</span>
        </div>
    `).join('');
}

/* --- [10] محرك استعراض وتدمير الأرشيف الجانبي (Archive Engine) --- */
async function loadHistoryToSidebar() {
    const sidebarList = document.getElementById('history-list');
    if (!sidebarList) return;

    try {
        sidebarList.innerHTML = `
            <div class="sync-loader" style="padding: 20px; text-align: center; color: var(--cyber-cyan); font-size: 0.75rem; font-family: monospace;">
                <i class="fa-solid fa-sync fa-spin"></i> SYNCING_VAULT...
            </div>`;

        const response = await fetch(CONFIG.HISTORY_URL);
        const data = await response.json();
        const sessions = data.sessions || [];

        if (sessions.length === 0) {
            sidebarList.innerHTML = `
                <div class="sidebar-user-header"><i class="fa-solid fa-user-shield"></i> <span>[ GUEST_NODE ]</span></div>
                <div class="empty-vault-msg" style="padding:20px; text-align:center; color:#444; font-family:monospace;">[EMPTY_VAULT]</div>`;
            return;
        }

        const username = (typeof CURRENT_USER_NAME !== 'undefined' && CURRENT_USER_NAME) ? CURRENT_USER_NAME : 'ROOT';
        sidebarList.innerHTML = `
            <div class="sidebar-user-header">
                <i class="fa-solid fa-user-shield"></i>
                <span>[ ${username.toUpperCase()} ]</span>
            </div>
            <div class="history-section-label" style="padding:15px; color:#666; font-size:0.75rem; font-family:monospace;">RECENT_ARCHIVES</div>
        `;

        sessions.forEach(session => {
            const card = document.createElement('div');
            card.className = 'history-card';
            const sID = session.session_uuid || session.session_id; // دعم كلا الاسمين
            card.innerHTML = `
                <div class="card-title-area" data-session-id="${sID}">
                    <i class="fa-regular fa-comment-dots card-icon"></i>
                    <span class="card-text">${session.title || 'NULL_SESSION'}</span>
                </div>
                <button class="restore-session-btn" data-session-id="${sID}" title="إعادة مزامنة">
                    <i class="fa-solid fa-rotate-right"></i>
                </button>
                <button class="delete-session-btn" data-session-id="${sID}" title="إتلاف البيانات">
                    <i class="fa-solid fa-trash-can"></i>
                </button>
            `;
            sidebarList.appendChild(card);
        });

        sidebarList.onclick = async (e) => {
            const titleTarget = e.target.closest('.card-title-area');
            const restoreTarget = e.target.closest('.restore-session-btn');
            const deleteTarget = e.target.closest('.delete-session-btn');

            if (titleTarget || restoreTarget) {
                const sID = (titleTarget || restoreTarget).getAttribute('data-session-id');
                console.log(`[SYSTEM]: Switching context to node: ${sID}`);
                chatState.sessionId = sID;
                sessionStorage.setItem(CONFIG.STORAGE_KEY, sID);
                
                loadHistoryFromServer(); // استدعاء فوري بدون تحديث الصفحة
                if (DOM.sidebar) DOM.sidebar.classList.remove('active');
            } else if (deleteTarget) {
                const sID = deleteTarget.getAttribute('data-session-id'); 
                if (confirm("⚠️ [SYSTEM_WARNING]: هل أنت متاكد؟")) {
                    try {
                        const delRes = await fetch(`delete_session.php?session_id=${sID}`);
                        if (delRes.ok) {
                            console.log(`[SYSTEM]: Node ${sID} purged from database.`);
                            await loadHistoryToSidebar(); 
                        }
                    } catch (err) {
                        console.error("[CRITICAL]: Purge operation failed.");
                    }
                }
            }
        };
    } catch (err) {
        sidebarList.innerHTML = '<div class="error-node">[SYNC_ERROR: ACCESS_DENIED]</div>';
        console.error("[VAULT_SYNC_ERROR]:", err);
    }
}

/* --- [11] ربط الجسور البرمجية على النطاق العالمي (Global Scope Bridging) --- */
window.executeCyberCommand = function(text) { handleSend(text); };
window.sendMessage = function(text) { handleSend(text); };
window.VoiceEngine = VoiceEngine; // تصدير محرك الصوت عالمياً
window.copyCode = Utils.copyCode; 
window.handleQuickCommand = function(text) {
    if (DOM.input) { 
        DOM.input.value = text; 
        DOM.input.focus();
        DOM.input.style.height = 'auto';
        DOM.input.style.height = DOM.input.scrollHeight + 'px';
        updateActionButton();
    }
};

// --- [12] محرك اختصارات لوحة المفاتيح للكمبيوتر (Desktop Shortcuts) ---
document.addEventListener('keydown', (e) => {
    // Ctrl + K لبدء محادثة جديدة
    if (e.ctrlKey && e.key.toLowerCase() === 'k') {
        e.preventDefault();
        Utils.createNewChat();
    }
    
    // Escape لإغلاق القوائم والنوافذ
    if (e.key === 'Escape') {
        if (DOM.sidebar?.classList.contains('active')) {
            DOM.sidebar.classList.remove('active');
        }
        if (DOM.profileModal?.classList.contains('active')) {
            DOM.profileModal.classList.remove('active');
            DOM.overlay?.classList.remove('active');
        }
        if (document.getElementById('attach-menu')?.classList.contains('active')) {
            document.getElementById('attach-menu').classList.remove('active');
        }
    }
});

// --- إدارة حالة الاتصال بالإنترنت بشكل صارم واحترافي ---
window.checkConnectionAndReload = async function() {
    const icon = document.querySelector('.alert-refresh-btn i');
    if (icon) icon.classList.add('fa-spin');

    try {
        // فحص الاتصال فعلياً عبر محاولة جلب الملف الرئيسي للسيرفر لضمان دقة الاستجابة
        const response = await fetch('index.php?ping=' + Date.now(), { 
            method: 'GET',
            cache: 'no-store' 
        });
        
        if (response.ok) {
            location.reload();
        } else {
            Utils.showNotification("النظام لا يزال غير متصل بالشبكة.", "error");
        }
    } catch (e) {
        Utils.showNotification("فشل مزامنة الشبكة، يرجى التحقق من الإشارة.", "error");
    } finally {
        if (icon) icon.classList.remove('fa-spin');
    }
};

function syncOfflineStatus() {
    const offlineScreen = document.getElementById('offline-screen');
    if (offlineScreen) {
        if (!navigator.onLine) {
            offlineScreen.style.display = 'flex';
        } else {
            offlineScreen.style.display = 'none';
        }
    }
}

window.addEventListener('offline', syncOfflineStatus);
window.addEventListener('online', syncOfflineStatus);
document.addEventListener('DOMContentLoaded', syncOfflineStatus);

// بدء تشغيل وإقلاع المنظومة بالكامل فور تحميل الـ DOM
document.addEventListener('DOMContentLoaded', bootstrap);

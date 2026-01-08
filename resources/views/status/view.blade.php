@extends('layouts.constant')

@section('content')
<div class="simple-status-viewer">
    <!-- Top Bar -->
    <div class="top-bar">
        <a href="{{ route('status') }}" class="back-btn">
            <i class="fas fa-arrow-left"></i>
        </a>
        <div class="user-info">
            <img src="{{ asset('images/profile_images/' . $status->profile_picture) }}"
                 alt="{{ $status->title }}"
                 class="user-pic">
            <div class="user-details">
                <h3>{{ $status->title }}</h3>
                <span>{{ $status->created_at->diffForHumans() }}</span>
            </div>
        </div>
    </div>

    <!-- Content Area -->
    <div class="content-area" id="contentArea">
        @if($status->type === 'image')
            <img src="{{ asset('images/status_images/' . $status->image) }}"
                 alt="Status"
                 class="status-img">
        @else
            <div class="status-text">
                {{ $status->content }}
            </div>
        @endif
    </div>

    <!-- Bottom Bar -->
    <div class="bottom-bar" id="bottomBar">
        @if($alreadyViewed)
            <div class="status-info-row already-earned">
                <i class="fas fa-check-circle"></i>
                <div class="status-info-text">
                    <strong>Earned UGX {{ number_format($user->getBonusFromStatus($status->id)) }}</strong>
                    <small>Already viewed</small>
                </div>
            </div>
        @elseif(!$canViewMore)
            <div class="status-info-row limit-reached">
                <i class="fas fa-times-circle"></i>
                <div class="status-info-text">
                    <strong>Daily Limit Reached</strong>
                    <small>2/2 views today</small>
                </div>
            </div>
        @else
            <div class="earn-section">
                <div class="earn-info">
                    <i class="fas fa-coins"></i>
                    <div class="earn-info-text">
                        <strong>UGX {{ number_format($potentialEarnings) }}</strong>
                        <small>{{ max(0, 2 - $todayViewsCount) }}/2 views left</small>
                    </div>
                </div>
                <button id="earnBtn" class="earn-btn" onclick="earnFromStatus({{ $status->id }})">
                    <i class="fas fa-bolt"></i> <span>EARN</span>
                </button>
            </div>

            @if(!$alreadyViewed && $canViewMore)
            <div class="timer-bar" id="timerBar">
                <div class="timer-progress"></div>
            </div>
            @endif
        @endif
    </div>

    <!-- Toast Notification -->
    <div class="simple-toast" id="toast">
        <i class="fas fa-check-circle"></i>
        <span>+UGX <span id="earnedAmount">0</span> earned!</span>
    </div>
</div>

<style>
/* Base Reset */
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

html, body {
    overflow: hidden;
    background: #000;
    height: 100%;
    width: 100%;
}

/* Main Container - Full Viewport */
.simple-status-viewer {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    width: 100%;
    height: 100%;
    background: #000;
    display: flex;
    flex-direction: column;
    overflow: hidden;
}

/* Top Bar */
.top-bar {
    flex: 0 0 auto;
    height: 60px;
    min-height: 60px;
    background: rgba(0, 0, 0, 0.95);
    border-bottom: 1px solid rgba(255, 255, 255, 0.1);
    display: flex;
    align-items: center;
    padding: 0 12px;
    gap: 10px;
    z-index: 10;
}

.back-btn {
    display: flex;
    align-items: center;
    justify-content: center;
    color: #fff;
    font-size: 18px;
    text-decoration: none;
    width: 40px;
    height: 40px;
    border-radius: 50%;
    transition: background 0.2s;
}

.back-btn:hover {
    background: rgba(255, 255, 255, 0.1);
}

.user-info {
    display: flex;
    align-items: center;
    gap: 10px;
    flex: 1;
    min-width: 0;
}

.user-pic {
    width: 40px;
    height: 40px;
    min-width: 40px;
    border-radius: 50%;
    object-fit: cover;
    border: 2px solid #25D366;
}

.user-details {
    min-width: 0;
    overflow: hidden;
}

.user-info h3 {
    color: #fff;
    font-size: 15px;
    font-weight: 600;
    margin: 0 0 2px 0;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.user-info span {
    color: rgba(255, 255, 255, 0.6);
    font-size: 12px;
    display: block;
}

/* Content Area - Fills Remaining Space */
.content-area {
    flex: 1 1 auto;
    display: flex;
    align-items: center;
    justify-content: center;
    overflow: hidden;
    padding: 10px;
    min-height: 0;
    background: #000;
}

.status-img {
    max-width: 100%;
    max-height: 100%;
    width: auto;
    height: auto;
    object-fit: contain;
    border-radius: 8px;
}

.status-text {
    color: #fff;
    font-size: clamp(16px, 4vw, 22px);
    text-align: center;
    line-height: 1.6;
    padding: 20px;
    max-width: 90%;
    word-wrap: break-word;
    overflow-wrap: break-word;
    hyphens: auto;
}

/* Bottom Bar */
.bottom-bar {
    flex: 0 0 auto;
    min-height: 70px;
    background: rgba(0, 0, 0, 0.95);
    border-top: 1px solid rgba(255, 255, 255, 0.1);
    padding: 12px 15px;
    display: flex;
    flex-direction: column;
    justify-content: center;
    z-index: 10;
}

/* Status Info Row (Already Earned / Limit Reached) */
.status-info-row {
    display: flex;
    align-items: center;
    gap: 12px;
}

.status-info-row i {
    font-size: 24px;
    flex-shrink: 0;
}

.status-info-row.already-earned i {
    color: #25D366;
}

.status-info-row.limit-reached i {
    color: #EF4444;
}

.status-info-text {
    min-width: 0;
}

.status-info-text strong {
    color: #fff;
    font-size: 15px;
    display: block;
    margin-bottom: 2px;
}

.status-info-text small {
    color: rgba(255, 255, 255, 0.6);
    font-size: 12px;
}

/* Earn Section */
.earn-section {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
}

.earn-info {
    display: flex;
    align-items: center;
    gap: 10px;
    flex: 1;
    min-width: 0;
}

.earn-info > i {
    color: #25D366;
    font-size: 22px;
    flex-shrink: 0;
}

.earn-info-text {
    min-width: 0;
}

.earn-info-text strong {
    color: #fff;
    font-size: 16px;
    display: block;
    margin-bottom: 2px;
}

.earn-info-text small {
    color: rgba(255, 255, 255, 0.6);
    font-size: 12px;
}

.earn-btn {
    display: none;
    align-items: center;
    justify-content: center;
    gap: 6px;
    background: #25D366;
    color: #fff;
    border: none;
    padding: 10px 20px;
    border-radius: 8px;
    font-size: 14px;
    font-weight: 600;
    cursor: pointer;
    white-space: nowrap;
    flex-shrink: 0;
    transition: background 0.2s, transform 0.1s;
}

.earn-btn:hover:not(:disabled) {
    background: #1DA851;
}

.earn-btn:active:not(:disabled) {
    transform: scale(0.98);
}

.earn-btn:disabled {
    opacity: 0.6;
    cursor: not-allowed;
}

.earn-btn.visible {
    display: flex;
}

/* Timer Bar */
.timer-bar {
    height: 3px;
    background: rgba(255, 255, 255, 0.15);
    border-radius: 3px;
    overflow: hidden;
    margin-top: 10px;
}

.timer-progress {
    height: 100%;
    width: 100%;
    background: linear-gradient(90deg, #25D366, #1DA851);
    animation: countdown 3s linear forwards;
    transform-origin: left;
}

@keyframes countdown {
    from { transform: scaleX(1); }
    to { transform: scaleX(0); }
}

/* Toast Notification */
.simple-toast {
    position: fixed;
    bottom: 90px;
    left: 50%;
    transform: translateX(-50%) translateY(20px);
    background: #25D366;
    color: #fff;
    padding: 12px 20px;
    border-radius: 25px;
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 14px;
    font-weight: 500;
    opacity: 0;
    visibility: hidden;
    transition: all 0.3s ease;
    z-index: 1000;
    box-shadow: 0 4px 15px rgba(37, 211, 102, 0.3);
    white-space: nowrap;
}

.simple-toast.show {
    opacity: 1;
    visibility: visible;
    transform: translateX(-50%) translateY(0);
}

.simple-toast i {
    font-size: 18px;
}

/* Responsive - Small Height Screens */
@media (max-height: 550px) {
    .top-bar {
        height: 50px;
        min-height: 50px;
        padding: 0 10px;
    }

    .user-pic {
        width: 35px;
        height: 35px;
        min-width: 35px;
    }

    .user-info h3 {
        font-size: 14px;
    }

    .bottom-bar {
        min-height: 60px;
        padding: 10px 12px;
    }

    .status-text {
        font-size: 14px;
        padding: 10px;
    }

    .simple-toast {
        bottom: 70px;
        padding: 10px 16px;
        font-size: 13px;
    }

    .earn-btn {
        padding: 8px 16px;
        font-size: 13px;
    }
}

/* Responsive - Narrow Screens */
@media (max-width: 360px) {
    .top-bar {
        padding: 0 8px;
        gap: 8px;
    }

    .back-btn {
        width: 36px;
        height: 36px;
        font-size: 16px;
    }

    .user-pic {
        width: 36px;
        height: 36px;
        min-width: 36px;
    }

    .user-info h3 {
        font-size: 13px;
    }

    .bottom-bar {
        padding: 10px;
    }

    .earn-btn {
        padding: 8px 14px;
        font-size: 12px;
    }

    .earn-info-text strong {
        font-size: 14px;
    }

    .status-info-text strong {
        font-size: 14px;
    }
}

/* Responsive - Landscape Mode */
@media (orientation: landscape) and (max-height: 450px) {
    .top-bar {
        height: 45px;
        min-height: 45px;
    }

    .bottom-bar {
        min-height: 55px;
        padding: 8px 12px;
    }

    .content-area {
        padding: 5px;
    }

    .status-text {
        font-size: 13px;
        padding: 8px;
        max-width: 80%;
    }

    .timer-bar {
        margin-top: 6px;
    }
}

/* Responsive - Tablet & Larger */
@media (min-width: 768px) {
    .top-bar {
        height: 70px;
        min-height: 70px;
        padding: 0 20px;
    }

    .user-pic {
        width: 48px;
        height: 48px;
        min-width: 48px;
    }

    .user-info h3 {
        font-size: 17px;
    }

    .user-info span {
        font-size: 13px;
    }

    .bottom-bar {
        min-height: 80px;
        padding: 15px 20px;
    }

    .status-text {
        max-width: 600px;
    }

    .earn-btn {
        padding: 12px 28px;
        font-size: 15px;
    }
}

/* Responsive - Large Screens */
@media (min-height: 900px) and (min-width: 600px) {
    .status-text {
        font-size: 24px;
        line-height: 1.7;
        max-width: 700px;
    }
}

/* Safe area for notched devices */
@supports (padding-bottom: env(safe-area-inset-bottom)) {
    .bottom-bar {
        padding-bottom: calc(12px + env(safe-area-inset-bottom));
    }

    .simple-toast {
        bottom: calc(90px + env(safe-area-inset-bottom));
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const canEarn = {{ (!$alreadyViewed && $canViewMore) ? 'true' : 'false' }};
    const alreadyViewed = {{ $alreadyViewed ? 'true' : 'false' }};
    const timerBar = document.getElementById('timerBar');
    const earnBtn = document.getElementById('earnBtn');

    if (canEarn) {
        // Show timer and start countdown
        if (timerBar) {
            timerBar.style.display = 'block';
        }

        // Show earn button after 3 seconds
        setTimeout(function() {
            if (earnBtn) {
                earnBtn.classList.add('visible');
            }
            if (timerBar) {
                timerBar.style.display = 'none';
            }
        }, 3000);
    } else {
        // Hide timer bar
        if (timerBar) {
            timerBar.style.display = 'none';
        }

        // If already viewed, show disabled button
        if (alreadyViewed && earnBtn) {
            earnBtn.classList.add('visible');
            earnBtn.disabled = true;
            earnBtn.innerHTML = '<i class="fas fa-check-circle"></i> <span>EARNED</span>';
        }
    }
});

function earnFromStatus(statusId) {
    const earnBtn = document.getElementById('earnBtn');
    const originalHTML = earnBtn.innerHTML;

    // Disable and show loading
    earnBtn.disabled = true;
    earnBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> <span>...</span>';

    fetch(`/status/${statusId}/earn`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Content-Type': 'application/json',
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showToast(data.earned);
            updateBottomBar(data.earned);
        } else {
            alert(data.message || 'An error occurred');
            earnBtn.innerHTML = originalHTML;
            earnBtn.disabled = false;
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Network error. Please try again.');
        earnBtn.innerHTML = originalHTML;
        earnBtn.disabled = false;
    });
}

function showToast(amount) {
    const toast = document.getElementById('toast');
    const amountSpan = document.getElementById('earnedAmount');

    amountSpan.textContent = amount.toLocaleString();
    toast.classList.add('show');

    setTimeout(() => {
        toast.classList.remove('show');
    }, 3000);
}

function updateBottomBar(earnedAmount) {
    const bottomBar = document.getElementById('bottomBar');

    bottomBar.innerHTML = `
        <div class="status-info-row already-earned">
            <i class="fas fa-check-circle"></i>
            <div class="status-info-text">
                <strong>Earned UGX ${earnedAmount.toLocaleString()}</strong>
                <small>Already viewed</small>
            </div>
        </div>
    `;
}
</script>
@endsection

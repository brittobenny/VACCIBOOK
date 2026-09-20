<!-- Feedback Button Start -->
<style>
  #feedback-btn-container {
    position: fixed;
    bottom: 20px;
    right: 20px;
    z-index: 9999;
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
  }

  #feedback-btn {
    position: relative;
    background: linear-gradient(135deg, #3b82f6, #6366f1);
    color: white;
    border: none;
    border-radius: 50px;
    cursor: pointer;
    padding: 12px;
    font-size: 16px;
    display: flex;
    align-items: center;
    box-shadow: 0 6px 15px rgba(59, 130, 246, 0.4);
    overflow: hidden;
    white-space: nowrap;
    user-select: none;
    transition:
      width 0.4s ease,
      padding 0.4s ease,
      box-shadow 0.3s ease,
      transform 0.3s ease;
    width: 55px; /* collapsed state */
    animation: float 3s ease-in-out infinite;
  }

  /* Expand on hover */
  #feedback-btn:hover {
    width: 210px;
    padding: 12px 25px;
    box-shadow: 0 12px 30px rgba(59, 130, 246, 0.6);
    transform: translateY(-4px) scale(1.05);
  }

  /* Shining animation overlay */
#feedback-btn::before {
  content: "";
  position: absolute;
  top: 0;
  left: -100%;
  width: 80%;
  height: 100%;
  background: linear-gradient(
    120deg,
    rgba(255,255,255,0) 0%,
    rgba(255,255,255,0.15) 40%,
    rgba(255,255,255,0.4) 50%,
    rgba(255,255,255,0.15) 60%,
    rgba(255,255,255,0) 100%
  );
  transform: skewX(-20deg);
  animation: shine 3.5s ease-in-out infinite;
  border-radius: 50px;
  filter: blur(2px);   /* smooth glow */
  pointer-events: none;
  z-index: 1;
}

@keyframes shine {
  0% {
    left: -100%;
    opacity: 0;
  }
  20% {
    opacity: 1;
  }
  50% {
    left: 120%;
    opacity: 1;
  }
  80% {
    opacity: 0;
  }
  100% {
    left: 120%;
    opacity: 0;
  }
}


  /* Ensure text and icon above shine */
  #feedback-btn > * {
    position: relative;
    z-index: 2;
  }

  #feedback-btn .btn-text {
    margin-left: 12px;
    font-weight: 600;
    opacity: 0;
    transition: opacity 0.3s ease;
  }

  /* Reveal text only when expanded */
  #feedback-btn:hover .btn-text {
    opacity: 1;
  }

  #feedback-btn .btn-icon {
    width: 22px;
    height: 22px;
    fill: white;
    flex-shrink: 0;
    transition: transform 0.3s ease;
  }

  /* Floating animation */
  @keyframes float {
    0%, 100% { transform: translateY(0); }
    50% { transform: translateY(-6px); }
  }

  @keyframes shine {
    0% { left: -80%; opacity: 0; }
    30% { opacity: 1; }
    60% { left: 120%; opacity: 1; }
    100% { left: 120%; opacity: 0; }
  }
</style>

<div id="feedback-btn-container" aria-label="Give us feedback button">
  <button id="feedback-btn" type="button" title="Give us feedback" onclick="window.location.href='feedback.php'">
    <!-- Feedback Icon: Speech bubble -->
    <svg class="btn-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" aria-hidden="true">
      <path d="M20 2H4a2 2 0 0 0-2 2v16l4-4h14a2 2 0 0 0 2-2V4a2 2 0 0 0-2-2zM6 11h12v2H6v-2zm0-3h12v2H6V8z"/>
    </svg>
    <span class="btn-text">Give Feedback</span>
  </button>
</div>
<!-- Feedback Button End -->

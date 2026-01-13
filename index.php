<?php
session_start();
require 'header.php';
?>

<style>
@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(30px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

@keyframes fadeInDown {
    from {
        opacity: 0;
        transform: translateY(-30px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

@keyframes scaleIn {
    from {
        opacity: 0;
        transform: scale(0.9);
    }
    to {
        opacity: 1;
        transform: scale(1);
    }
}

@keyframes slideInLeft {
    from {
        opacity: 0;
        transform: translateX(-50px);
    }
    to {
        opacity: 1;
        transform: translateX(0);
    }
}

@keyframes slideInRight {
    from {
        opacity: 0;
        transform: translateX(50px);
    }
    to {
        opacity: 1;
        transform: translateX(0);
    }
}

@keyframes float {
    0%, 100% {
        transform: translate3d(0, 0, 0);
    }
    50% {
        transform: translate3d(0, -20px, 0);
    }
}

.hero-section {
    background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 50%, #ffffff 100%);
    padding: 100px 0 80px;
    position: relative;
    overflow: hidden;
}

.hero-section::before {
    content: '';
    position: absolute;
    top: -50%;
    right: -10%;
    width: 600px;
    height: 600px;
    background: radial-gradient(circle, rgba(128, 0, 0, 0.03) 0%, transparent 70%);
    border-radius: 50%;
    animation: float 6s ease-in-out infinite;
    will-change: transform;
    transform: translateZ(0);
    backface-visibility: hidden;
}

.hero-section::after {
    content: '';
    position: absolute;
    bottom: -50%;
    left: -10%;
    width: 500px;
    height: 500px;
    background: radial-gradient(circle, rgba(128, 0, 0, 0.02) 0%, transparent 70%);
    border-radius: 50%;
    animation: float 8s ease-in-out infinite reverse;
    will-change: transform;
    transform: translateZ(0);
    backface-visibility: hidden;
}

.hero-content {
    position: relative;
    z-index: 1;
}

.hero-title {
    font-size: 3.5rem;
    font-weight: 700;
    background: var(--primary-gradient);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
    margin-bottom: 20px;
    animation: fadeInDown 0.8s cubic-bezier(0.16, 1, 0.3, 1);
    line-height: 1.2;
}

.hero-subtitle {
    color: var(--text-secondary);
    font-size: 1.25rem;
    max-width: 700px;
    margin: 0 auto 40px;
    line-height: 1.8;
    animation: fadeInUp 0.8s cubic-bezier(0.16, 1, 0.3, 1) 0.2s backwards;
}

.hero-buttons {
    animation: fadeInUp 0.8s cubic-bezier(0.16, 1, 0.3, 1) 0.4s backwards;
    display: flex;
    gap: 15px;
    justify-content: center;
    flex-wrap: wrap;
}

.features-section {
    padding: 80px 0;
    background: var(--bg-color);
}

.features-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
    gap: 30px;
    margin-top: 50px;
}

.feature-card {
    background: white;
    padding: 40px 30px;
    border-radius: var(--radius-lg);
    text-align: center;
    transition: all 0.4s cubic-bezier(0.16, 1, 0.3, 1);
    border: 1px solid rgba(0, 0, 0, 0.05);
    position: relative;
    overflow: hidden;
    animation: scaleIn 0.6s cubic-bezier(0.16, 1, 0.3, 1) backwards;
}

.feature-card:nth-child(1) {
    animation-delay: 0.1s;
}

.feature-card:nth-child(2) {
    animation-delay: 0.2s;
}

.feature-card:nth-child(3) {
    animation-delay: 0.3s;
}

.feature-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 4px;
    background: var(--primary-gradient);
    transform: scaleX(0);
    transition: transform 0.4s cubic-bezier(0.16, 1, 0.3, 1);
}

.feature-card:hover {
    transform: translateY(-10px);
    box-shadow: var(--shadow-lg);
}

.feature-card:hover::before {
    transform: scaleX(1);
}

.feature-icon {
    width: 80px;
    height: 80px;
    background: var(--primary-gradient);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 25px;
    color: white;
    font-size: 2rem;
    transition: all 0.4s cubic-bezier(0.16, 1, 0.3, 1);
    box-shadow: 0 8px 20px rgba(128, 0, 0, 0.2);
}

.feature-card:hover .feature-icon {
    transform: scale(1.1) rotate(5deg);
    box-shadow: 0 12px 30px rgba(128, 0, 0, 0.3);
}

.feature-title {
    font-size: 1.4rem;
    font-weight: 600;
    color: var(--primary-color);
    margin-bottom: 15px;
}

.feature-description {
    color: var(--text-secondary);
    line-height: 1.7;
}

.steps-section {
    padding: 80px 0;
    background: white;
}

.section-title {
    text-align: center;
    font-size: 2.5rem;
    font-weight: 700;
    color: var(--primary-color);
    margin-bottom: 15px;
    animation: fadeInDown 0.6s cubic-bezier(0.16, 1, 0.3, 1);
}

.section-subtitle {
    text-align: center;
    color: var(--text-secondary);
    font-size: 1.1rem;
    margin-bottom: 50px;
    animation: fadeInUp 0.6s cubic-bezier(0.16, 1, 0.3, 1) 0.1s backwards;
}

.steps-container {
    max-width: 900px;
    margin: 0 auto;
}

.step-item {
    display: flex;
    gap: 30px;
    margin-bottom: 40px;
    animation: slideInLeft 0.6s cubic-bezier(0.16, 1, 0.3, 1) backwards;
}

.step-item:nth-child(even) {
    flex-direction: row-reverse;
    animation: slideInRight 0.6s cubic-bezier(0.16, 1, 0.3, 1) backwards;
}

.step-item:nth-child(1) {
    animation-delay: 0.1s;
}

.step-item:nth-child(2) {
    animation-delay: 0.2s;
}

.step-item:nth-child(3) {
    animation-delay: 0.3s;
}

.step-number {
    width: 60px;
    height: 60px;
    background: var(--primary-gradient);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 1.8rem;
    font-weight: 700;
    flex-shrink: 0;
    box-shadow: 0 4px 15px rgba(128, 0, 0, 0.3);
    transition: all 0.3s ease;
}

.step-item:hover .step-number {
    transform: scale(1.15);
}

.step-content {
    flex: 1;
}

.step-title {
    font-size: 1.3rem;
    font-weight: 600;
    color: var(--text-primary);
    margin-bottom: 10px;
}

.step-description {
    color: var(--text-secondary);
    line-height: 1.7;
}

@media (max-width: 768px) {
    .hero-title {
        font-size: 2.5rem;
    }
    
    .hero-subtitle {
        font-size: 1.1rem;
    }
    
    .features-grid {
        grid-template-columns: 1fr;
    }
    
    .step-item,
    .step-item:nth-child(even) {
        flex-direction: column;
    }
}
</style>

<div class="hero-section">
    <div class="container">
        <div class="hero-content" style="text-align: center;">
            <h1 class="hero-title">Fee Management System</h1>
            <p class="hero-subtitle">
                Streamline your fee receipt submissions with our secure, fast, and efficient platform. 
                Upload receipts instantly and track approvals in real-time.
            </p>
            <div class="hero-buttons">
                <?php if(isset($_SESSION['user_id'])): ?>
                    <a href="dashboard.php" class="btn btn-primary">
                        <i class="fas fa-tachometer-alt" style="margin-right: 8px;"></i> Go to Dashboard
                    </a>
                    <a href="logout.php" class="btn btn-outline">
                        <i class="fas fa-sign-out-alt" style="margin-right: 8px;"></i> Logout
                    </a>
                <?php else: ?>
                    <a href="login.php" class="btn btn-primary">
                        <i class="fas fa-sign-in-alt" style="margin-right: 8px;"></i> Login
                    </a>
                    <a href="signup.php" class="btn btn-outline">
                        <i class="fas fa-user-plus" style="margin-right: 8px;"></i> Sign Up
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<section class="features-section">
    <div class="container">
        <h2 class="section-title">Why Choose Our System?</h2>
        <p class="section-subtitle">Experience seamless fee management with powerful features</p>
        
        <div class="features-grid">
            <div class="feature-card">
                <div class="feature-icon">
                    <i class="fas fa-bolt"></i>
                </div>
                <h3 class="feature-title">Lightning Fast</h3>
                <p class="feature-description">
                    Upload your fee receipts instantly. Our optimized system processes submissions in seconds.
                </p>
            </div>
            
            <div class="feature-card">
                <div class="feature-icon">
                    <i class="fas fa-shield-alt"></i>
                </div>
                <h3 class="feature-title">Secure & Safe</h3>
                <p class="feature-description">
                    Your documents are encrypted and stored securely with industry-standard protection.
                </p>
            </div>
            
            <div class="feature-card">
                <div class="feature-icon">
                    <i class="fas fa-bell"></i>
                </div>
                <h3 class="feature-title">Real-Time Updates</h3>
                <p class="feature-description">
                    Get instant email notifications when your submission status changes.
                </p>
            </div>
        </div>
    </div>
</section>

<section class="steps-section">
    <div class="container">
        <h2 class="section-title">How It Works</h2>
        <p class="section-subtitle">Three simple steps to submit your fee receipt</p>
        
        <div class="steps-container">
            <div class="step-item">
                <div class="step-number">1</div>
                <div class="step-content">
                    <h3 class="step-title">Upload Your Receipt</h3>
                    <p class="step-description">
                        Students log in and upload their fee receipts (PDF, JPG, or PNG) through an intuitive dashboard. 
                        Select your academic year and semester for organized tracking.
                    </p>
                </div>
            </div>
            
            <div class="step-item">
                <div class="step-number">2</div>
                <div class="step-content">
                    <h3 class="step-title">Admin Review</h3>
                    <p class="step-description">
                        Administrators receive your submission and review it carefully. They can approve or reject 
                        submissions with detailed remarks for transparency.
                    </p>
                </div>
            </div>
            
            <div class="step-item">
                <div class="step-number">3</div>
                <div class="step-content">
                    <h3 class="step-title">Track & Download</h3>
                    <p class="step-description">
                        Monitor your submission status in real-time. Once approved, download your verified receipt 
                        anytime with a unique tracking ID for your records.
                    </p>
                </div>
            </div>
        </div>
    </div>
</section>

<?php require 'footer.php'; ?>

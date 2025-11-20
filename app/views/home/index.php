<?php
$config = require __DIR__ . '/../../../config/config.php';
$base = rtrim($config['app']['base_url'], '/');
?>

<!-- =========== Section Héros =========== -->
<main class="hero-section" id="home">
    <div class="hero-content">
        <h1>Connect. Act. Inspire.</h1>
        <p>Share stories, join local initiatives, and connect through positive interactions. Building peace, one connection at a time.</p>
        <div class="hero-buttons">
            <a href="<?= $base ?>/?controller=auth&action=create" class="btn-hero-primary">Get Started</a>
            <a href="<?= $base ?>/?controller=auth&action=index" class="btn-hero-secondary">Login</a>
        </div>
    </div>
</main>

<!-- =========== Section Mission =========== -->
<section class="mission-section">
    <div class="mission-container">
        <h2>Our Mission</h2>
        <div class="mission-grid">
            <!-- Carte 1: Share Stories -->
            <div class="mission-card">
                <div class="mission-icon icon-blue">
                    <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path></svg>
                </div>
                <h3>Share Stories</h3>
                <p>Express yourself and connect with others through powerful personal narratives that inspire change.</p>
            </div>
            <!-- Carte 2: Join Initiatives -->
            <div class="mission-card">
                <div class="mission-icon icon-green">
                    <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path></svg>
                </div>
                <h3>Join Initiatives</h3>
                <p>Participate in local community projects focused on ecology, solidarity, and education.</p>
            </div>
            <!-- Carte 3: Build Community -->
            <div class="mission-card">
                <div class="mission-icon icon-orange">
                     <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M23 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path></svg>
                </div>
                <h3>Build Community</h3>
                <p>Connect with like-minded individuals and create meaningful relationships that matter.</p>
            </div>
        </div>
    </div>
</section>

<!-- =========== Section Stories =========== -->
<section class="content-section" id="stories">
    <div class="section-container">
        <h2 class="section-title">Stories</h2>
        <p class="section-subtitle">Discover inspiring stories from our community</p>
        <div class="stories-grid">
            <article class="story-card">
                <div class="story-header">
                    <div class="story-author">
                        <div class="story-avatar">JD</div>
                        <div>
                            <div class="story-author-name">Jane Doe</div>
                            <div class="story-date">2 days ago</div>
                        </div>
                    </div>
                </div>
                <div class="story-content">
                    <h3>My Journey to Peace</h3>
                    <p>After years of working in conflict zones, I've learned that peace starts with small acts of kindness. This community has shown me that change is possible when we come together.</p>
                </div>
            </article>

            <article class="story-card">
                <div class="story-header">
                    <div class="story-author">
                        <div class="story-avatar">MS</div>
                        <div>
                            <div class="story-author-name">Michael Smith</div>
                            <div class="story-date">5 days ago</div>
                        </div>
                    </div>
                </div>
                <div class="story-content">
                    <h3>Building Bridges in My Neighborhood</h3>
                    <p>Started a weekly community garden project that brought together people from different backgrounds. The conversations we have while planting seeds are just as important as the vegetables we grow.</p>
                </div>
            </article>

            <article class="story-card">
                <div class="story-header">
                    <div class="story-author">
                        <div class="story-avatar">AL</div>
                        <div>
                            <div class="story-author-name">Anna Lee</div>
                            <div class="story-date">1 week ago</div>
                        </div>
                    </div>
                </div>
                <div class="story-content">
                    <h3>Education Changes Everything</h3>
                    <p>Teaching children about empathy and understanding has transformed not just their lives, but mine too. Every lesson is a step toward a more peaceful world.</p>
                </div>
            </article>

            <article class="story-card">
                <div class="story-header">
                    <div class="story-author">
                        <div class="story-avatar">RB</div>
                        <div>
                            <div class="story-author-name">Robert Brown</div>
                            <div class="story-date">2 weeks ago</div>
                        </div>
                    </div>
                </div>
                <div class="story-content">
                    <h3>From Conflict to Collaboration</h3>
                    <p>What started as a disagreement with my neighbor became a beautiful friendship. We now organize community events together, proving that understanding can overcome any difference.</p>
                </div>
            </article>

            <article class="story-card">
                <div class="story-header">
                    <div class="story-author">
                        <div class="story-avatar">SG</div>
                        <div>
                            <div class="story-author-name">Sarah Green</div>
                            <div class="story-date">3 weeks ago</div>
                        </div>
                    </div>
                </div>
                <div class="story-content">
                    <h3>The Power of Listening</h3>
                    <p>Sometimes the most powerful thing we can do is simply listen. This platform has taught me that every voice matters and every story deserves to be heard.</p>
                </div>
            </article>

            <article class="story-card">
                <div class="story-header">
                    <div class="story-author">
                        <div class="story-avatar">TW</div>
                        <div>
                            <div class="story-author-name">Thomas White</div>
                            <div class="story-date">1 month ago</div>
                        </div>
                    </div>
                </div>
                <div class="story-content">
                    <h3>Reconnecting with Nature</h3>
                    <p>Our environmental initiative has not only helped the planet but also brought our community closer. Working together for a common cause creates bonds that last a lifetime.</p>
                </div>
            </article>
        </div>
    </div>
</section>

<!-- =========== Section Initiatives =========== -->
<section class="content-section" id="initiatives">
    <div class="section-container">
        <h2 class="section-title">Initiatives</h2>
        <p class="section-subtitle">Join local projects and make a difference</p>
        <div class="initiatives-grid">
            <div class="initiative-card">
                <div class="initiative-category category-ecology">Ecology</div>
                <h3>Community Garden Project</h3>
                <p>Transform unused urban spaces into thriving community gardens. Learn sustainable farming practices while building connections with neighbors.</p>
                <a href="<?= $base ?>/?controller=auth&action=create" class="btn-initiative">Join Initiative</a>
            </div>

            <div class="initiative-card">
                <div class="initiative-category category-solidarity">Solidarity</div>
                <h3>Food Bank Volunteers</h3>
                <p>Help distribute food to families in need. Every contribution matters in supporting our community's most vulnerable members.</p>
                <a href="<?= $base ?>/?controller=auth&action=create" class="btn-initiative">Join Initiative</a>
            </div>

            <div class="initiative-card">
                <div class="initiative-category category-education">Education</div>
                <h3>After-School Tutoring</h3>
                <p>Support children's learning by volunteering as a tutor. Share your knowledge and help shape the next generation.</p>
                <a href="<?= $base ?>/?controller=auth&action=create" class="btn-initiative">Join Initiative</a>
            </div>

            <div class="initiative-card">
                <div class="initiative-category category-ecology">Ecology</div>
                <h3>Beach Cleanup Day</h3>
                <p>Join us for a monthly beach cleanup to protect our oceans. Together we can make a significant environmental impact.</p>
                <a href="<?= $base ?>/?controller=auth&action=create" class="btn-initiative">Join Initiative</a>
            </div>

            <div class="initiative-card">
                <div class="initiative-category category-solidarity">Solidarity</div>
                <h3>Elderly Care Program</h3>
                <p>Spend time with elderly community members, providing companionship and support. Your presence can brighten someone's day.</p>
                <a href="<?= $base ?>/?controller=auth&action=create" class="btn-initiative">Join Initiative</a>
            </div>

            <div class="initiative-card">
                <div class="initiative-category category-education">Education</div>
                <h3>Digital Literacy Workshop</h3>
                <p>Teach essential digital skills to community members. Help bridge the digital divide and empower others with technology.</p>
                <a href="<?= $base ?>/?controller=auth&action=create" class="btn-initiative">Join Initiative</a>
            </div>
        </div>
    </div>
</section>

<!-- =========== Section Participations =========== -->
<section class="content-section" id="participations">
    <div class="section-container">
        <h2 class="section-title">Participations</h2>
        <p class="section-subtitle">See how our community is making an impact</p>
        <div class="participations-grid">
            <div class="participation-card">
                <div class="participation-header">
                    <div class="participation-avatar">JD</div>
                    <div class="participation-info">
                        <div class="participation-name">Jane Doe</div>
                        <div class="participation-date">3 days ago</div>
                    </div>
                </div>
                <div class="participation-content">
                    <h4>Community Garden Project</h4>
                    <p>joined this event</p>
                </div>
            </div>

            <div class="participation-card">
                <div class="participation-header">
                    <div class="participation-avatar">MS</div>
                    <div class="participation-info">
                        <div class="participation-name">Michael Smith</div>
                        <div class="participation-date">1 week ago</div>
                    </div>
                </div>
                <div class="participation-content">
                    <h4>Food Bank Volunteers</h4>
                    <p>contributed to this initiative</p>
                </div>
            </div>

            <div class="participation-card">
                <div class="participation-header">
                    <div class="participation-avatar">AL</div>
                    <div class="participation-info">
                        <div class="participation-name">Anna Lee</div>
                        <div class="participation-date">2 weeks ago</div>
                    </div>
                </div>
                <div class="participation-content">
                    <h4>After-School Tutoring</h4>
                    <p>helped organize this event</p>
                </div>
            </div>

            <div class="participation-card">
                <div class="participation-header">
                    <div class="participation-avatar">RB</div>
                    <div class="participation-info">
                        <div class="participation-name">Robert Brown</div>
                        <div class="participation-date">2 weeks ago</div>
                    </div>
                </div>
                <div class="participation-content">
                    <h4>Beach Cleanup Day</h4>
                    <p>joined this event</p>
                </div>
            </div>

            <div class="participation-card">
                <div class="participation-header">
                    <div class="participation-avatar">SG</div>
                    <div class="participation-info">
                        <div class="participation-name">Sarah Green</div>
                        <div class="participation-date">3 weeks ago</div>
                    </div>
                </div>
                <div class="participation-content">
                    <h4>Elderly Care Program</h4>
                    <p>contributed to this initiative</p>
                </div>
            </div>

            <div class="participation-card">
                <div class="participation-header">
                    <div class="participation-avatar">TW</div>
                    <div class="participation-info">
                        <div class="participation-name">Thomas White</div>
                        <div class="participation-date">1 month ago</div>
                    </div>
                </div>
                <div class="participation-content">
                    <h4>Digital Literacy Workshop</h4>
                    <p>helped organize this event</p>
                </div>
            </div>
        </div>
    </div>
</section>


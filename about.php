<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>About - Abaarso Tech University</title>

<script src="https://cdn.tailwindcss.com"></script>
<link href="https://unpkg.com/aos@2.3.4/dist/aos.css" rel="stylesheet">

<style>
:root{
    --uni-color:#E30B5C; /* Vivid Raspberry */
}

/* Parallax Hero */
.parallax {
    background-image: url('https://images.unsplash.com/photo-1523050854058-8df90110c9f1');
    min-height: 65vh;
    background-attachment: fixed;
    background-position: center;
    background-repeat: no-repeat;
    background-size: cover;
}

/* Timeline Line */
.timeline-line {
    position:absolute;
    left:50%;
    width:4px;
    height:100%;
    background:var(--uni-color);
    transform:translateX(-50%);
}

/* Lightbox */
.lightbox {
    display:none;
    position:fixed;
    inset:0;
    background:rgba(0,0,0,0.9);
    justify-content:center;
    align-items:center;
    z-index:9999;
}
.lightbox img{
    max-width:90%;
    max-height:90%;
}

/* Counter Style */
.counter {
    font-size:3rem;
    font-weight:bold;
    color:var(--uni-color);
}
</style>
</head>

<body class="bg-gradient-to-br from-[#9B0036] via-[#C2185B] to-[#E30B5C] text-white overflow-x-hidden">

<!-- Particle Background -->
<div id="particles-js" class="fixed inset-0 -z-10"></div>

<!-- NAV -->
<nav class="flex justify-between items-center px-8 py-4 bg-black/40 backdrop-blur-md">
    <h2 class="text-xl font-bold text-[var(--uni-color)]">
        Abaarso Tech University
    </h2>
    <div class="space-x-6">
        <a href="index.php" class="hover:text-[var(--uni-color)]">Home</a>
        <a href="login.php" class="hover:text-[var(--uni-color)]">Login</a>
    </div>
</nav>

<!-- HERO -->
<section class="parallax flex items-center justify-center text-center">
    <div class="bg-black/60 p-10 rounded-xl">
        <h1 class="text-5xl font-bold mb-4">
            About <span class="text-[var(--uni-color)]">ATU</span>
        </h1>
        <p class="max-w-2xl">
            Inspiring Innovation. Empowering Future Leaders.
        </p>
    </div>
</section>

<!-- COUNTERS -->
<section class="py-16 bg-slate-800 text-center">
    <div class="grid md:grid-cols-3 gap-8 max-w-5xl mx-auto">
        <div data-aos="fade-up">
            <div class="counter" data-target="3500">0</div>
            <p>Students Enrolled</p>
        </div>
        <div data-aos="fade-up" data-aos-delay="200">
            <div class="counter" data-target="1200">0</div>
            <p>Graduates</p>
        </div>
        <div data-aos="fade-up" data-aos-delay="400">
            <div class="counter" data-target="4">0</div>
            <p>Campuses</p>
        </div>
    </div>
</section>

<!-- VIDEO SECTION -->
<section class="py-20 px-6 text-center">
    <h2 class="text-4xl font-bold mb-10">
        Campus <span class="text-[var(--uni-color)]">Experience</span>
    </h2>

    <div class="max-w-4xl mx-auto" data-aos="zoom-in">
        <div class="relative pb-[56.25%] h-0 overflow-hidden rounded-xl shadow-2xl">
            <iframe class="absolute top-0 left-0 w-full h-full"
                src="vidoe.mp4"
                frameborder="0"
                allowfullscreen>
            </iframe>
        </div>
    </div>
</section>

<!-- TIMELINE -->
<section class="py-20 relative max-w-6xl mx-auto px-6">
<h2 class="text-4xl text-center font-bold mb-16">
University <span class="text-[var(--uni-color)]">Journey</span>
</h2>

<div class="relative">
<div class="timeline-line"></div>

<div class="space-y-16">

<div class="relative w-1/2 pr-8 text-right" data-aos="fade-right">
<h3 class="text-2xl text-[var(--uni-color)] font-bold">2010</h3>
<p>University Founded in Hargeisa</p>
</div>

<div class="relative w-1/2 ml-auto pl-8" data-aos="fade-left">
<h3 class="text-2xl text-[var(--uni-color)] font-bold">2015</h3>
<p>Expansion to Multiple Campuses</p>
</div>

<div class="relative w-1/2 pr-8 text-right" data-aos="fade-right">
<h3 class="text-2xl text-[var(--uni-color)] font-bold">2020</h3>
<p>Research & Innovation Center Established</p>
</div>

</div>
</div>
</section>

<!-- GALLERY -->
<section class="py-20 bg-slate-800">
<h2 class="text-4xl text-center font-bold mb-12">
Campus <span class="text-[var(--uni-color)]">Gallery</span>
</h2>

<div class="grid md:grid-cols-3 gap-6 px-6 max-w-6xl mx-auto">

<img src="image.jpg"
class="rounded-xl cursor-pointer hover:scale-105 transition"
onclick="openLightbox(this.src)">

<img src="images.jpeg"
class="rounded-xl cursor-pointer hover:scale-105 transition"
onclick="openLightbox(this.src)">

<img src="image.jpg"
class="rounded-xl cursor-pointer hover:scale-105 transition"
onclick="openLightbox(this.src)">

</div>
</section>

<!-- LEADERSHIP -->
<section class="py-20 text-center max-w-4xl mx-auto px-6">
<h2 class="text-4xl font-bold mb-10">
University <span class="text-[var(--uni-color)]">Leadership</span>
</h2>

<div class="bg-slate-800 p-10 rounded-2xl shadow-xl" data-aos="fade-up">
<img src="president.jpeg"
class="w-40 h-40 rounded-full mx-auto mb-6 object-cover">

<h3 class="text-2xl font-bold text-[var(--uni-color)]">
Dr. Ahmed Hussein Ese
</h3>
<p class="text-gray-400">Founder & President</p>
</div>
</section>

<!-- LIGHTBOX -->
<div id="lightbox" class="lightbox" onclick="closeLightbox()">
<img id="lightbox-img">
</div>

<!-- FOOTER -->
<footer class="text-center py-6 text-gray-400">
© <?php echo date("Y"); ?> Abaarso Tech University
</footer>

<!-- Scripts -->
<script src="https://cdn.jsdelivr.net/npm/particles.js@2.0.0/particles.min.js"></script>
<script>
particlesJS("particles-js",{
  particles:{ number:{ value:60 }, color:{ value:"#E30B5C" }, shape:{ type:"circle" },
  opacity:{ value:0.5 }, size:{ value:3 }, move:{ enable:true, speed:2 }},
});
</script>

<script src="https://unpkg.com/aos@2.3.4/dist/aos.js"></script>
<script>AOS.init({duration:1000});</script>

<script>
// Counter Animation
const counters=document.querySelectorAll('.counter');
counters.forEach(counter=>{
counter.innerText='0';
const updateCounter=()=>{
const target=+counter.getAttribute('data-target');
const c=+counter.innerText;
const increment=target/200;
if(c<target){
counter.innerText=`${Math.ceil(c+increment)}`;
setTimeout(updateCounter,10);
}else{
counter.innerText=target+"+";
}
};
updateCounter();
});

// Lightbox
function openLightbox(src){
document.getElementById("lightbox").style.display="flex";
document.getElementById("lightbox-img").src=src;
}
function closeLightbox(){
document.getElementById("lightbox").style.display="none";
}
</script>

</body>
</html>
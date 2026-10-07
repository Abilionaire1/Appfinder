/* ============ AppFinder — PIXEL ARCADE prototype interactions ============ */
(function(){
  "use strict";
  var $  = function(s){ return document.querySelector(s); };
  var $$ = function(s){ return Array.prototype.slice.call(document.querySelectorAll(s)); };
  var PLAT = {WEB:"Web",WINDOWS:"Windows",MACOS:"macOS",LINUX:"Linux",IOS:"iOS",ANDROID:"Android",DESKTOP:"Desktop",MOBILE:"Mobile"};
  var proseDefault;

  /* ---------- app data for the detail view ---------- */
  var TAGS = { free:["t-free","Free"], open:["t-open","Open Source"], edu:["t-edu","Edu"], priv:["t-priv","Privacy-friendly"], ent:["t-ent","Fun"] };
  var APPS = {
    figma:      { letter:"F", tier:"ptile--pink", rating:"4.7", name:"Figma",            tagline:"Collaborative design platform",           cat:"Design & collaboration",  platforms:"WEB · MACOS · WINDOWS · IOS", tags:["free","edu","priv"], pricing:"Free plan available", edu:"Yes — full features", oss:"No",  offline:"Limited" },
    notion:     { letter:"N", tier:"",           rating:"4.8", name:"Notion",           tagline:"Workspace for notes, docs & wikis",       cat:"Workspace & notes",       platforms:"WEB · IOS · ANDROID · DESKTOP", tags:["free","edu","priv"], pricing:"Free plan available", edu:"Yes — Plus free", oss:"No",  offline:"Yes" },
    spotify:    { letter:"S", tier:"ptile--mint", rating:"4.5", name:"Spotify",         tagline:"Music & podcast streaming",               cat:"Music & podcasts",        platforms:"WEB · IOS · ANDROID · DESKTOP", tags:["free","ent"], pricing:"Free, ad-supported",  edu:"Premium discount",  oss:"No",  offline:"Premium only" },
    vscode:     { letter:"V", tier:"ptile--blue", rating:"4.8", name:"VS Code",         tagline:"Open-source code editor",                 cat:"Code editor",             platforms:"WINDOWS · MACOS · LINUX", tags:["free","open"], pricing:"Free, forever",       edu:"n/a — it is free",  oss:"Yes — MIT core", offline:"Yes" },
    krita:      { letter:"K", tier:"ptile--viol", rating:"4.6", name:"Krita",           tagline:"Digital painting, pro-grade & free",      cat:"Graphics & illustration", platforms:"WINDOWS · MACOS · LINUX", tags:["free","open","edu"], pricing:"Free, forever",       edu:"Widely used in art classes", oss:"Yes — GPL", offline:"Yes" },
    obsidian:   { letter:"O", tier:"",           rating:"4.6", name:"Obsidian",        tagline:"Markdown notes on a private graph",       cat:"Notes & thinking",        platforms:"WINDOWS · MACOS · LINUX · MOBILE", tags:["free","priv","edu"], pricing:"Free for personal use", edu:"Free with edu email", oss:"No",  offline:"Yes — local files" },
    audacity:   { letter:"A", tier:"",           rating:"4.4", name:"Audacity",        tagline:"The classic open audio editor",           cat:"Audio editing",           platforms:"WINDOWS · MACOS · LINUX", tags:["free","open"], pricing:"Free, forever",       edu:"Classroom staple",  oss:"Yes — GPL", offline:"Yes" },
    libreoffice:{ letter:"L", tier:"",           rating:"4.5", name:"LibreOffice",     tagline:"Full office suite without the license",   cat:"Office suite",            platforms:"WINDOWS · MACOS · LINUX", tags:["free","open","edu"], pricing:"Free, forever",       edu:"Document formats first", oss:"Yes — MPL", offline:"Yes" },
    gimp:       { letter:"G", tier:"",           rating:"4.5", name:"GIMP",            tagline:"Raster graphics editor since 1996",       cat:"Image editing",           platforms:"WINDOWS · MACOS · LINUX", tags:["free","open"], pricing:"Free, forever",       edu:"Long classroom history", oss:"Yes — GPL", offline:"Yes" },
    davinci:    { letter:"D", tier:"ptile--red", rating:"4.7", name:"DaVinci Resolve", tagline:"Pro video editing & color grading",       cat:"Video editing",           platforms:"WINDOWS · MACOS · LINUX", tags:["free","open"], pricing:"Free version",        edu:"Used in film schools", oss:"Lite — GPL", offline:"Yes" },
    kdenlive:   { letter:"K", tier:"ptile--viol", rating:"4.5", name:"Kdenlive",        tagline:"Open-source video editor by KDE",         cat:"Video editing",           platforms:"WINDOWS · MACOS · LINUX", tags:["free","open"], pricing:"Free, forever",       edu:"Great first NLE",   oss:"Yes — GPL", offline:"Yes" },
    shotcut:    { letter:"S", tier:"ptile--blue", rating:"4.3", name:"Shotcut",         tagline:"Simple face, serious timeline",           cat:"Video editing",           platforms:"WINDOWS · MACOS · LINUX", tags:["free","open"], pricing:"Free, forever",       edu:"Low learning curve", oss:"Yes — GPL", offline:"Yes" },
    capcut:     { letter:"C", tier:"ptile--pink", rating:"4.5", name:"CapCut",          tagline:"Free editor for social content",          cat:"Video editing",           platforms:"WINDOWS · MACOS · MOBILE", tags:["free","ent"], pricing:"Free with account",   edu:"Check data defaults", oss:"No", offline:"Yes" },
    openshot:   { letter:"O", tier:"ptile--mint", rating:"4.2", name:"OpenShot",        tagline:"The friendliest first editor",            cat:"Video editing",           platforms:"WINDOWS · MACOS · LINUX", tags:["free","open","edu"], pricing:"Free, forever",       edu:"Made for beginners", oss:"Yes — GPL", offline:"Yes" },
    penpot:     { letter:"P", tier:"ptile--mint", rating:"4.4", name:"Penpot",          tagline:"Open-source design & prototyping",        cat:"Design & collaboration",  platforms:"WEB · DESKTOP", tags:["free","open"], pricing:"Free, forever",        edu:"Self-hostable for schools", oss:"Yes — MPL", offline:"Self-host only" },
    lunacy:     { letter:"L", tier:"ptile--blue", rating:"4.4", name:"Lunacy",          tagline:"Offline-capable UI design",               cat:"Design & collaboration",  platforms:"WINDOWS · MACOS", tags:["free"], pricing:"Free, forever",          edu:"Works without internet", oss:"No", offline:"Yes — by design" },
    sketch:     { letter:"S", tier:"",           rating:"4.3", name:"Sketch",          tagline:"Mac-native UI design",                    cat:"Design & collaboration",  platforms:"MACOS · WEB", tags:["priv"], pricing:"Paid · edu discount",  edu:"Yes — edu discount", oss:"No", offline:"Partial" }
  };

  /* ---------- theme ---------- */
  var root = document.documentElement;
  var savedTheme = null;
  try { savedTheme = localStorage.getItem("af-theme"); } catch(e){}
  var initial = savedTheme || (window.matchMedia && window.matchMedia("(prefers-color-scheme: dark)").matches ? "dark" : "light");
  setTheme(initial);
  function setTheme(t){
    root.setAttribute("data-theme", t);
    try { localStorage.setItem("af-theme", t); } catch(e){}
    $(".ic-sun").style.display  = t === "dark" ? "block" : "none";
    $(".ic-moon").style.display = t === "dark" ? "none"  : "block";
  }
  $("#themeToggle").addEventListener("click", function(){
    setTheme(root.getAttribute("data-theme") === "dark" ? "light" : "dark");
  });

  /* ---------- detail renderer ---------- */
  function renderDetail(slug){
    var a = APPS[slug] || APPS.figma;
    var mg = $("#detailMonogram");
    mg.textContent = a.letter;
    mg.className = "ptile ptile--lg " + a.tier;
    $("#detailName").textContent     = a.name;
    $("#detailTagline").textContent  = a.tagline;
    $("#detailPlatforms").textContent= a.platforms;
    $("#detailRating").textContent   = a.rating;
    $("#crumbCat").textContent       = a.cat;
    $("#crumbName").textContent      = a.name;
    var tr = $("#detailTags"); tr.innerHTML = "";
    a.tags.forEach(function(k){
      var s = document.createElement("span");
      s.className = "tag " + TAGS[k][0]; s.textContent = TAGS[k][1];
      tr.appendChild(s);
    });
    var rows = $("#view-detail .spec-table");
    var proseEl = $("#panel-overview .prose");
    if (slug === "figma" && proseDefault) {
      proseEl.outerHTML = proseDefault;
    } else {
      var platList = a.platforms.split(" · ").map(function(x){ return PLAT[x] || x; }).join(", ").toLowerCase();
      proseEl.innerHTML =
        '<p><b>' + a.name + '</b> — ' + a.tagline.toLowerCase()
        + '. Sorted into the ' + a.cat.toLowerCase() + ' world, playable on ' + platList
        + ', and verified against the official source before it earned a spot in the index.</p>'
        + '<p>&ldquo;Free&rdquo; in this index means something specific: the button above links to the official website only, '
        + 'pricing is quoted from the vendor, and the education perks are recorded in the stats panel beside. '
        + 'If any of it drifts, the entry goes <mark>back to the checkpoint</mark> for a re-run.</p>';
    }
    rows.rows[0].cells[1].textContent = a.pricing;
    rows.rows[1].cells[1].textContent = a.cat;
    rows.rows[2].cells[1].textContent = a.platforms.split(" · ").map(function(p){ return PLAT[p] || p; }).join(" · ");
    rows.rows[3].cells[1].textContent = a.edu;   rows.rows[3].cells[1].className = a.edu.indexOf("Yes") === 0 ? "yes" : "";
    rows.rows[4].cells[1].textContent = a.oss === "No" ? "No" : a.oss;
    rows.rows[4].cells[1].className   = a.oss.indexOf("Yes") === 0 || a.oss.indexOf("Lite") === 0 ? "yes" : "no";
    var save = $("#saveBtn");
    save.classList.remove("is-saved"); save.textContent = "+ Save to list";
    switchTab("overview");
  }

  /* ---------- tabs ---------- */
  function switchTab(name){
    $$("#view-detail .tab").forEach(function(t){
      var on = t.getAttribute("data-tab") === name;
      t.classList.toggle("is-active", on);
      t.setAttribute("aria-selected", on ? "true" : "false");
    });
    $$("#view-detail .tab-panel").forEach(function(p){
      p.hidden = p.id !== "panel-" + name;
    });
  }
  $$("#view-detail .tab").forEach(function(t){
    t.addEventListener("click", function(){ switchTab(t.getAttribute("data-tab")); });
  });

  /* ---------- views / router ---------- */
  var VIEWS = { home:"view-home", detail:"view-detail", results:"view-results" };
  function showView(key){
    Object.keys(VIEWS).forEach(function(k){
      document.getElementById(VIEWS[k]).classList.toggle("is-active", k === key);
    });
    closeMenu();
    var bb = { home:"home", search:"search", detail:"search", results:"search" };
    $$(".bottombar button").forEach(function(b){
      b.classList.toggle("is-active", b.getAttribute("data-bnav") === bb[key]);
    });
  }
  function route(){
    var h = location.hash || "#/";
    if (h.indexOf("#/#") === 0) {                      /* home + anchor, e.g. #/#notes */
      document.title = "AppFinder — Press Start. 0¢ Forever.";
      showView("home");
      var anchor = h.slice(3);
      var target = document.getElementById(anchor);
      if (target) { requestAnimationFrame(function(){ target.scrollIntoView({behavior:"smooth"}); }); }
      return;
    }
    if (h.indexOf("#/app/") === 0) {
      var slug = h.slice(6);
      renderDetail(slug);
      document.title = (APPS[slug] || APPS.figma).name + " — AppFinder";
      showView("detail"); window.scrollTo({top:0}); return;
    }
    if (h.indexOf("#/search") === 0) {
      document.title = "Search — AppFinder";
      if (currentQuery) { $("#queryEcho").textContent = "\u201C" + currentQuery + "\u201D"; }
      showView("results"); window.scrollTo({top:0}); return;
    }
    document.title = "AppFinder — Press Start. 0¢ Forever.";
    showView("home");
  }
  window.addEventListener("hashchange", route);

  /* ---------- search ---------- */
  var currentQuery = "";
  $("#searchForm").addEventListener("submit", function(e){
    e.preventDefault();
    currentQuery = $("#searchInput").value.trim() || "everything";
    if (location.hash === "#/search") { route(); } else { location.hash = "#/search"; }
  });
  document.addEventListener("click", function(e){
    var q = e.target.closest("[data-q]");
    if (!q) return;
    currentQuery = q.getAttribute("data-q");
    if (q.tagName !== "A") {
      if (location.hash === "#/search") { route(); } else { location.hash = "#/search"; }
    }
  });
  document.addEventListener("keydown", function(e){
    if (e.key === "/" && !/input|textarea|select/i.test(document.activeElement.tagName)) {
      e.preventDefault();
      if (document.getElementById("view-home").classList.contains("is-active")) {
        $("#searchInput").focus();
      }
    }
    if (e.key === "Escape") { closeDrawer(); closeMenu(); }
  });

  /* ---------- app navigation via delegation ---------- */
  document.addEventListener("click", function(e){
    var hp = e.target.closest(".helpful");
    if (hp) {
      e.preventDefault(); e.stopPropagation();
      hp.classList.toggle("is-on");
      hp.textContent = hp.classList.contains("is-on") ? "+2 Helpful" : "+1 Helpful";
      return;
    }
    var bm = e.target.closest(".bookmark");
    if (bm) {
      e.preventDefault(); e.stopPropagation();
      bm.classList.toggle("is-on");
      return;
    }
    var el = e.target.closest("[data-app]");
    if (el && !e.target.closest("a")) {
      location.hash = "#/app/" + el.getAttribute("data-app");
      return;
    }
  });
  document.addEventListener("keydown", function(e){
    if (e.key !== "Enter") return;
    var el = e.target.closest ? e.target.closest('[role="link"][data-app], [role="link"][data-q]') : null;
    if (el) { el.click(); }
  });

  /* ---------- save button (detail) ---------- */
  $("#saveBtn").addEventListener("click", function(){
    this.classList.toggle("is-saved");
    this.textContent = this.classList.contains("is-saved") ? "Saved \u2713" : "+ Save to list";
  });

  /* ---------- drawer ---------- */
  function openDrawer(){ document.body.classList.add("drawer-open"); $("#allFiltersBtn").classList.add("is-open"); }
  function closeDrawer(){ document.body.classList.remove("drawer-open"); var b=$("#allFiltersBtn"); b && b.classList.remove("is-open"); }
  $("#allFiltersBtn").addEventListener("click", openDrawer);
  $("#drawerClose").addEventListener("click", closeDrawer);
  $("#drawerBackdrop").addEventListener("click", closeDrawer);
  $("#drawerApply").addEventListener("click", function(){
    closeDrawer();
    var cl = $("#countLine");
    if (cl) { cl.innerHTML = "<b>9 RESULTS</b> \u00B7 FILTERED \u00B7 0.02S"; }
  });
  $("#drawerReset").addEventListener("click", function(){
    $$("#drawer input[type=checkbox], #drawer input[type=radio]").forEach(function(i){
      i.checked = i.type === "radio" ? i.parentElement.textContent.trim() === "Any" : false;
    });
  });

  /* ---------- mobile menu ---------- */
  var menuBtn = $("#menuBtn");
  function closeMenu(){
    $("#mMenu").classList.remove("is-open");
    menuBtn.setAttribute("aria-expanded","false");
  }
  menuBtn.addEventListener("click", function(){
    var open = $("#mMenu").classList.toggle("is-open");
    menuBtn.setAttribute("aria-expanded", open ? "true" : "false");
  });
  $$("#mMenu a").forEach(function(a){ a.addEventListener("click", closeMenu); });

  /* ---------- bottom bar ---------- */
  $$(".bottombar button").forEach(function(b){
    b.addEventListener("click", function(){
      var k = b.getAttribute("data-bnav");
      if (k === "home")   { location.hash = "#/"; }
      if (k === "search") { location.hash = "#/search"; }
      if (k === "saved")  { location.hash = "#/"; requestAnimationFrame(function(){ var f=$("#featured"); f && f.scrollIntoView({behavior:"smooth"}); }); }
      if (k === "menu")   { menuBtn.click(); }
    });
  });

  /* ---------- boot ---------- */
  proseDefault = $("#panel-overview .prose").outerHTML;
  renderDetail("figma");
  route();
})();

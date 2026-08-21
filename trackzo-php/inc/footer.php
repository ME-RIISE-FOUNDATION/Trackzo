    </main>
  </div>
</div>

<script>
var _sb = function(){ return document.getElementById('sidebar'); };
var _bd = function(){ return document.getElementById('sb-backdrop'); };
var _isDesktop = function(){ return window.matchMedia('(min-width:1024px)').matches; };

// Mobile drawer
function openSidebar(){ _sb().classList.remove('-translate-x-full'); _bd().classList.remove('hidden'); }
function closeSidebar(){ _sb().classList.add('-translate-x-full'); _bd().classList.add('hidden'); }

// Desktop icon-rail collapse
function toggleCollapse(){
  var sb = _sb();
  sb.classList.toggle('is-collapsed');
  try { localStorage.setItem('tz_sb', sb.classList.contains('is-collapsed') ? '1' : '0'); } catch(e){}
}

// The chevron button: collapses on desktop, closes the drawer on mobile
function chevronClick(){ if (_isDesktop()) { toggleCollapse(); } else { closeSidebar(); } }

// Restore desktop collapse preference
(function(){
  try { if (localStorage.getItem('tz_sb') === '1') _sb().classList.add('is-collapsed'); } catch(e){}
})();
// Close the mobile drawer if the viewport grows to desktop
window.addEventListener('resize', function(){ if (_isDesktop()) closeSidebar(); });
</script>
</body>
</html>

<!-- footer.php -->
<footer class="bg-blue-800 text-white text-center text-lg py-3 fixed bottom-0 w-full z-50">
  <div id="live-clock" class="font-bold"></div>
  <div class="text-sm">Powered by Twinsofte.com. All rights reserved.</div>

  <script>
    function updateClock() {
      const now = new Date();
      const date = now.toLocaleDateString('en-GB');
      const time = now.toLocaleTimeString('en-GB');
      document.getElementById('live-clock').textContent = `${date} - ${time}`;
    }
    setInterval(updateClock, 1000);
    updateClock();
  </script>
</footer>

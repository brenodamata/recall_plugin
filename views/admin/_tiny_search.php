  <form action="<?php echo $this->url_for('search')?>" method="post">
  <p class="search-box">
  <label for="am_query">Search Recall Database: </label><input type="text" id="rm_query" name="rm_query" placeholder="Enter any search term">
  <select name="rm_type">
    <option value="recall">Search Recalls</option>
  </select>
  </p>
  </form>

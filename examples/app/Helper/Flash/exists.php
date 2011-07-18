Check whether or not messages are in a given queue and display them if they are.
<?php if ($this->flash('error')->exists()): ?>
<p>There are errors!</p>
<?php endif; ?>
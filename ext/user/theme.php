<?php

class UserPageTheme extends Themelet {
	public function display_login_page(Page $page) {
		$page->set_title("Login");
		$page->set_heading("Login");
		$page->add_block(new NavBlock());
		$page->add_block(new Block("Login There",
			"There should be a login box to the left"));
	}

	public function display_user_list(Page $page, $users, User $user) {
		$page->set_title("User List");
		$page->set_heading("User List");
		$page->add_block(new NavBlock());
		$html = "<table>";
		$html .= "<tr><td>Name</td></tr>";
		foreach($users as $duser) {
			$html .= "<tr>";
			$html .= "<td><a href='".make_link("user/".url_escape($duser->name))."'>".html_escape($duser->name)."</a></td>";
			$html .= "</tr>";
		}
		$html .= "</table>";
		$page->add_block(new Block("Users", $html));
	}

	public function display_user_links(Page $page, User $user, $parts) {
		# $page->add_block(new Block("User Links", join(", ", $parts), "main", 10));
	}

	public function display_user_block(Page $page, User $user, $parts) {
		$h_name = html_escape($user->name);
		$html = 'Logged in as '.$h_name;
		foreach($parts as $part) {
			$html .= '<br><a href="'.$part["link"].'">'.$part["name"].'</a>';
		}
		$page->add_block(new Block("User Links", $html, "left", 90));
	}

	public function display_signup_page(Page $page) {
		global $config;
		$tac = $config->get_string("login_tac", "");

		if($config->get_bool("login_tac_bbcode")) {
			$tfe = new TextFormattingEvent($tac);
			send_event($tfe);
			$tac = $tfe->formatted;
		}

		if(empty($tac)) {$html = "";}
		else {$html = '<p>'.$tac.'</p>';}

		$h_reca = "<tr><td colspan='2'>".captcha_get_html()."</td></tr>";

		$html .= '
		'.make_form(make_link("user_admin/create"))."
			<table style='width: 300px;' class='form'>
				<tbody>
					<tr><td>Name</td><td><input type='text' name='name'></td></tr>
					<tr><td>Password</td><td><input type='password' name='pass1'></td></tr>
					<tr><td>Repeat Password</td><td><input type='password' name='pass2'></td></tr>
					<tr><td>Email (Optional)</td><td><input type='text' name='email'></td></tr>
					$h_reca
				</tbody>
				<tfoot>
					<tr><td colspan='2'><input type='Submit' value='Create Account'></td></tr>
				</tfoot>
			</table>
		</form>
		";

		$page->set_title("Create Account");
		$page->set_heading("Create Account");
		$page->add_block(new NavBlock());
		$page->add_block(new Block("Signup", $html));
	}

	public function display_signups_disabled(Page $page) {
		$page->set_title("Signups Disabled");
		$page->set_heading("Signups Disabled");
		$page->add_block(new NavBlock());
		$page->add_block(new Block("Signups Disabled",
			"The board admin has disabled the ability to create new accounts~"));
	}

	public function display_login_block(Page $page) {
		global $config;
		$html = '
			'.make_form(make_link("user_admin/login"))."
				<table class='form'>
					<tbody>
						<tr>
							<th><label for='user'>Name</label></th>
							<td><input id='user' type='text' name='user'></td>
						</tr>
						<tr>
							<th><label for='pass'>Password</label></th>
							<td><input id='pass' type='password' name='pass'></td>
						</tr>
					</tbody>
					<tfoot>
						<tr><td colspan='2'><input type='submit' value='Log In'></td></tr>
					</tfoot>
				</table>
			</form>
		";
		if($config->get_bool("login_signup_enabled")) {
			$html .= "<small><a href='".make_link("user_admin/create")."'>Create Account</a></small>";
		}
		$page->add_block(new Block("Login", $html, "left", 90));
	}

	public function display_ip_list(Page $page, $uploads, $comments) {
		$html = "<table id='ip-history'>";
		$html .= "<tr><td>Uploaded from: ";
		$n = 0;
		foreach($uploads as $ip => $count) {
			$html .= '<br>'.$ip.' ('.$count.')';
			if(++$n >= 20) {
				$html .= "<br>...";
				break;
			}
		}

		$html .= "</td><td>Commented from:";
		$n = 0;
		foreach($comments as $ip => $count) {
			$html .= '<br>'.$ip.' ('.$count.')';
			if(++$n >= 20) {
				$html .= "<br>...";
				break;
			}
		}

		$html .= "</td></tr>";
		$html .= "<tr><td colspan='2'>(Most recent at top)</td></tr></table>";

		$page->add_block(new Block("IPs", $html));
	}

	public function display_user_page(User $duser, $stats) {
		global $page, $user;
		assert(is_array($stats));
		$stats[] = 'User ID: '.$duser->id;

		$page->set_title(html_escape($duser->name)."'s Page");
		$page->set_heading(html_escape($duser->name)."'s Page");
		$page->add_block(new NavBlock());
		$page->add_block(new Block("Stats", join("<br>", $stats), "main", 0));

		if(!$user->is_anonymous()) {
			if($user->id == $duser->id || $user->can("change_user_info")) {
				$page->add_block(new Block("Options", $this->build_options($duser), "main", 20));
			}
		}
	}

	protected function build_options(User $duser) {
		global $config, $database, $user;
		$html = "";
		if($duser->id != $config->get_int('anon_id')){  //justa fool-admin protection so they dont mess around with anon users.
		
			$html .= "
			".make_form(make_link("user_admin/change_pass"))."
				<input type='hidden' name='id' value='{$duser->id}'>
				<table style='width: 300px;' class='form'>
					<thead>
						<tr><th colspan='2'>Change Password</th></tr>
					</thead>
					<tbody>
						<tr><th>Password</th><td><input type='password' name='pass1'></td></tr>
						<tr><th>Repeat Password</th><td><input type='password' name='pass2'></td></tr>
					</tbody>
					<tfoot>
						<tr><td colspan='2'><input type='Submit' value='Change Password'></td></tr>
					</tfoot>
				</table>
			</form>

			<p>".make_form(make_link("user_admin/change_email"))."
				<input type='hidden' name='id' value='{$duser->id}'>
				<table style='width: 300px;' class='form'>
					<thead><tr><th colspan='2'>Change Email</th></tr></thead>
					<tbody><tr><th>Address</th><td><input type='text' name='address' value='".html_escape($duser->email)."'></td></tr></tbody>
					<tfoot><tr><td colspan='2'><input type='Submit' value='Set'></td></tr></tfoot>
				</table>
			</form>
			";

			if($user->class->name == "admin") {
				$i_user_id = int_escape($duser->id);
				$h_is_admin = $duser->is_admin() ? " checked" : "";
				$html .= "
					<p>".make_form(make_link("user_admin/change_class"))."
						<input type='hidden' name='id' value='$i_user_id'>
						Class: <select name='class'>
				";
				global $_user_classes;
				foreach($_user_classes as $name => $values) {
					$h_name = html_escape($name);
					$h_title = html_escape(ucwords($name));
					$h_selected = ($name == $duser->class->name ? " selected" : "");
					$html .= "<option value='$h_name'$h_selected>$h_title</option>\n";
				}
				$html .= "
						</select>
						<input type='submit' value='Set'>
					</form>
					
					".make_form(make_link("user_admin/delete_user"))."
					<input type='hidden' name='id' value='$i_user_id'>
					<input type='submit' value='Delete User' onclick='confirm(\"Delete the user?\");' />
					</form>
					
					".make_form(make_link("user_admin/delete_user_with_images"))."
					<input type='hidden' name='id' value='$i_user_id'>
					<input type='submit' value='Delete User with images' onclick='confirm(\"Delete the user with his uploaded images?\");' />
					</form>
				";
			}
		}
		return $html;
	}
// }}}
}
?>

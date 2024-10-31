<section id="wrap_section">
	<div>
	<section id="widgets" class="card oimwc_faq">
		<header><h3>Frequently Asked Questions</h3></header>
		<div id="faq">
		<ul class="clearfix">
		<li>
			<p>All submitted data will not be saved and is used solely for the purposes your support request. You will not be added to a mailing list, solicited without your permission, nor will your site be administered after this support case is closed.
			</p>
		</li>
		</ul>
		</div>
	</section>

<section id="contact_form" class="message embed wp-core-ui relative">
	<form id="oimwc_contact_form" method="post">
		<div>
		<fieldset>
			<label class="iconed-input name"><i class="fa fa-user"></i>
				<input type="text" name="name" id="contact_name" value="" placeholder="First and Last Name" required></label>
			<label class="iconed-input"><i class="fa fa-envelope fa-lg"></i>
				<input type="email" name="email" id="contact_email" value="" placeholder="Your Email Address" required></label>
			
			<ul class="subjects iconed-input support_subjects">
			<li>
				<label><input type="radio" name="subject" id="technical_support_radio" value="Technical Support" required> Technical Support</label>
			</li>
			<li>
				<label><input type="radio" name="subject" id="billing_issue_radio" value="Billing Issue" required> Billing Issue</label>
			</li>
			<li>
				<label><input type="radio" name="subject" id="feature_request_radio" value="Feature Request" required> Feature Request</label>
			</li>
			<li>
				<label><input type="radio" name="subject" id="pre_sale_question_radio" value="Pre-Sale Question" required> Pre-Sale Question</label>
			</li>
			<li>
				<label><input type="radio" name="subject" id="press_radio" value="Press" required> Press</label>
			</li>
			<li>
				<label><input type="radio" name="subject" id="bug_radio" value="Bug" required> Bug</label>
			</li>
			</ul>
		</fieldset>
		<div class="technical_support_content" style="display: none;">
			<fieldset class="site other_support_content" style="display: block;">
				<div>
					<label class="iconed-input site"><i class="fas fa-globe"></i>
						<input type="text" name="domain" id="contact_domain" value="<?php echo get_site_url();?>" placeholder="Your Site Address (E.g http://my.address.com)" required></label>
				</div>
			</fieldset>
			<fieldset class="message-box" style="display: block;">
				<div>
					<label class="iconed-input site"><i class="fas fa-th-large"></i>
						<input type="text" name="summary" id="contact_summary" value="" placeholder="Summary (In 10 words or less, summarize your issue or question)" required></label>
					<label id="contact_msg" class="iconed-input textarea">
					<i class="fas fa-pencil-alt"></i>
					<textarea name="message" id="contact_message" cols="44" rows="10" placeholder="Please describe the issue you are having. Be detailed but brief." required></textarea>
					</label>
				</div>
			</fieldset>
			<div class="other_support_content">
			<fieldset class="site" style="display: block;">
				<div>
					<p style="margin-top: 10px;">If it's about a specific page on your site, please add the relevant link.</p>
					<label class="iconed-input site"><i class="fas fa-globe"></i>
						<input type="text" name="link" value="" placeholder="Relevant Page on Your Site (E.g. https://dev.stylingwebben.se/relevant-page/)"></label>
				</div>
			</fieldset>
			<fieldset class="expandable closed">
				<h4 class="title"><span>WordPress Login</span></h4>
				<div class="wp_login_details" style="display: none;">
					<label class="iconed-input site"><i class="fas fa-globe"></i>
						<input type="text" name="admin_login_page" id="admin_login_page" value="" placeholder="Your Admin Page Login URL (E.g http://my.address.com/wp-admin)" required>
					</label>
					<label class="iconed-input admin-login"><i class="fa fa-user"></i>
						<input type="text" name="wp_admin_user" value="" placeholder="Username" autocomplete="new-username">
					</label>
					<label class="iconed-input admin-password"><i class="fas fa-lock"></i>
						<input type="password" name="wp_admin_password" value="" placeholder="Password" autocomplete="new-password">
					</label>
					<p class="note">Instead of providing your primary admin account, create a new admin that can be disabled when the support case is closed.</p>
				</div>
			</fieldset>
			</div>
		</div>
		</div>

		<footer>
			<button class="primary large button-primary submit_contact"><span>Submit</span></button>
		</footer>
	</form>
	<div class="message-sent">
		<p>Your message has been sent! We'll get back to you as soon as we can.</p>
	</div>
	<div class="message-not-sent">
		<p>Something went wrong. Please try again.</p>
	</div>
</section>
</div>
</section>
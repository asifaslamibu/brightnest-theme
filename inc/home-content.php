<?php
/**
 * Homepage copy: defaults for the homepage settings, the long text for the Home page editor,
 * the homepage SEO title, and a one-time step that puts this copy in place.
 *
 * @package BrightNest
 */

defined( 'ABSPATH' ) || exit;

/**
 * Homepage defaults (merged into brightnest_defaults()).
 */
function brightnest_home_defaults() {
	return array(
		'home_seo_title' => 'Bright Nest Cleaning: Professional Maid, Deep, Home & Office Cleaning Services in Dubai',
		'home_seo_desc'  => 'Bright Nest Cleaning offers maid, deep, home, and office cleaning with customized plans, flexible scheduling, eco-friendly products, trained staff, and 24/7 same-day service in Dubai.',
		'hero_more'      => 'We offer a complete range of residential and commercial cleaning solutions tailored to your unique needs, ensuring spotless spaces, a healthier environment, and complete peace of mind.',

		'show_intro'    => '1',
		'show_clean'    => '1',
		'show_maint'    => '1',
		'show_blog'     => '1',
		'show_article'  => '1',
		'show_home_faq' => '1',

		'intro_title'  => 'Your Trusted Partner for a |Cleaner Home & Office',
		'intro_text'   => "Bright Nest Cleaning Co. is committed to delivering high-quality cleaning and property maintenance solutions across Dubai and the UAE.\n\nFrom regular home cleaning and deep cleaning to hourly maid services and comprehensive property maintenance, we handle every task with care, professionalism, and attention to detail.",
		'intro_points' => "100% Customer Satisfaction Guarantee\nSafe and Eco-Friendly Cleaning Practices\n24/7 Customer Support\nFree Site Inspections and Quotations",

		'clean_title' => 'Professional Cleaning Services for |Homes, Offices & Businesses',
		'clean_sub'   => 'We provide reliable, high-quality cleaning services for homes, offices, and businesses, creating clean, hygienic, and comfortable environments.',
		'clean_list'  => implode(
			"\n",
			array(
				'Maid Services in Dubai|Trained and professional maids available on hourly, weekly, or monthly plans to keep your home clean, organized, and comfortable. Enjoy reliable, flexible, and efficient maid services tailored to your household needs and schedule.|/service/home-cleaning/',
				'House Cleaning in Dubai|Enjoy a spotless, fresh, and comfortable home with our professional house cleaning services. We thoroughly clean bedrooms, living areas, kitchens, and bathrooms, removing dust, dirt, and grime to maintain a clean, hygienic, and welcoming living environment.|/service/home-cleaning/',
				'Deep Cleaning in Dubai|Our professional deep cleaning services target hidden dirt, stubborn grime, and hard-to-reach areas to leave your space thoroughly clean and refreshed. Ideal for seasonal cleaning, special occasions, or routine property care, we help create a spotless, hygienic, and healthier environment for your home or workplace.|/service/deep-cleaning/',
				'Office Cleaning in Dubai|Keep your workplace clean, hygienic, and welcoming with our professional office cleaning services. We provide thorough cleaning solutions for offices and commercial spaces, helping maintain a healthy, organized, and productive environment for your employees and visitors.',
				'Restaurant Cleaning in Dubai|Maintain a spotless, hygienic, and welcoming environment with our professional restaurant cleaning services. We provide specialized cleaning solutions for dining areas, kitchens, and food service spaces, helping businesses maintain high cleanliness standards and create a safe, comfortable experience for guests and staff.',
				'Industrial Cleaning in Dubai|Maintain a clean, safe, and well-organized workplace with our professional industrial cleaning services. We provide specialized cleaning solutions for warehouses, factories, workshops, and other industrial facilities, effectively removing dust, debris, grease, and accumulated dirt to support a healthier and safer working environment.',
				'Move-In / Move-Out Cleaning in Dubai|Make every move easier with our professional move-in and move-out cleaning services. We thoroughly clean and sanitize your property, including kitchens, bathrooms, bedrooms, and living areas, ensuring a fresh and spotless space. Ideal for tenants, landlords, homeowners, and property managers preparing for handovers, new occupants, or relocation.|/service/deep-cleaning/',
				'Pest Control in Dubai|Protect your home and business with Bright Nest Cleaning Co.’s professional pest control services in Dubai. Our solutions help address common pest problems and maintain a cleaner, safer, and more comfortable environment for your family, employees, and customers.|/service/pest-control/',
			)
		),

		'maint_title' => 'Reliable Property Maintenance to Keep Your Home & Business in |Top Condition',
		'maint_sub'   => 'Reliable maintenance solutions, including AC servicing, electrical repairs, plumbing, painting, and handyman services, to keep your home and business in excellent condition.',
		'maint_list'  => implode(
			"\n",
			array(
				'AC Maintenance in Dubai|Keep your air conditioning system running smoothly with our professional AC maintenance services. From AC cleaning and gas refills to routine system inspections and maintenance, we help improve airflow, enhance cooling performance, and support energy efficiency for year-round comfort.|/service/ac-cleaning/',
				'Electrical Maintenance in Dubai|Keep your property safe and your electrical systems running efficiently with our professional electrical maintenance services. We handle faulty wiring, switches, lighting, and electrical connections, providing reliable solutions for residential and commercial properties with a focus on safety, quality, and efficiency.|/service/handyman-maintenance/',
				'Plumbing Repairs in Dubai|Keep your plumbing systems functioning smoothly with our professional plumbing repair services. We provide reliable solutions for leak detection, pipe repairs, and fixture installations, helping prevent water damage and maintain properly functioning kitchens, bathrooms, and other essential areas of your property.|/service/handyman-maintenance/',
				'Tile Work in Dubai|Enhance your property\'s appearance and functionality with our professional tile installation and repair services. We work with floor and wall tiles to deliver neat finishes, durable results, and stylish designs that complement your space while ensuring quality workmanship and long-lasting performance.|/service/handyman-maintenance/',
				'Painting Services in Dubai|Transform your space with our professional wall and ceiling painting services. We deliver smooth finishes, clean workmanship, and quality results to refresh your interiors, enhance your property\'s appearance, and create a brighter, more inviting atmosphere for your home or business.|/service/handyman-maintenance/',
				'General Handyman Services in Dubai|Keep your property in excellent condition with our reliable general handyman services. From furniture assembly and repairs to curtain rod installation, wall mounting, and other minor fixes, our skilled team handles everyday maintenance tasks efficiently and professionally, helping keep your home or workplace functional, comfortable, and well-maintained.|/service/handyman-maintenance/',
				'Interior Design Services in Dubai|Transform your home or workplace with our professional interior design services. We create elegant, functional, and visually appealing spaces tailored to your style, preferences, and lifestyle. From thoughtful space planning to carefully selected design elements, we help bring your vision to life while enhancing comfort, functionality, and overall appeal.',
			)
		),

		'numbers_title' => 'Premium Cleaning & Maintenance Solutions for |Spotless Results',
		'numbers_sub'   => 'We deliver professional cleaning and property maintenance services with a commitment to quality, reliability, and customer satisfaction.',

		'reviews_eyebrow' => 'Testimonials',
		'reviews_title'   => 'What Our Customers |Say About Us',
		'blog_eyebrow'    => 'Blog',
		'blog_title'      => 'Latest News, |Tips & Insights',

		'home_faq_title' => 'Frequently Asked Questions About |Cleaning Services in Dubai',
		'home_faqs'      => implode(
			"\n",
			array(
				'How much do cleaning services cost in Dubai?|The cost depends on the type of cleaning, property size, service duration, and specific requirements. Contact Bright Nest Cleaning Co. for a quotation based on your needs.',
				'Can I book maid services on an hourly basis?|Hourly maid services may be available depending on the provider\'s current plans and scheduling arrangements. Contact Bright Nest Cleaning Co. to confirm availability, minimum booking duration, and pricing.',
				'What is included in a deep cleaning service?|Deep cleaning may include detailed cleaning of kitchens, bathrooms, floors, tiles, and other areas where dirt and grime accumulate. The exact scope depends on the selected package and should be confirmed before booking.',
				'Do you provide office cleaning services in Dubai?|Bright Nest Cleaning Co. offers office cleaning solutions among its service categories. Contact the company to confirm service availability in Dubai, the areas covered, and the arrangements suitable for your workplace.',
				'Can I arrange cleaning for a move-in or move-out?|Move-in and move-out cleaning is designed to help prepare a property for new occupants or a handover. The required tasks and quotation will depend on the property\'s size, condition, and cleaning needs.',
				'How can I book a cleaning appointment?|You can explore the available services on the Bright Nest Cleaning Co. website and contact the team to discuss your requirements, request a quotation, and confirm an appointment.',
				'How do I choose the right cleaning company in Dubai?|Compare service options, customer reviews, pricing transparency, staff professionalism, booking terms, and the company\'s ability to meet your specific requirements. Confirm service coverage and any relevant licensing or insurance details before making your decision.',
			)
		),
	);
}

/**
 * Settings that already had older text: the new copy replaces them once (see brightnest_home_seed()).
 */
function brightnest_home_replaced() {
	return array(
		'hero_title'    => 'We Are One of the Best Cleaning Service Providers in |Dubai',
		'hero_subtitle' => 'From reliable maid services and deep cleaning to professional office cleaning and expert property maintenance, Bright Nest Cleaning Co. delivers exceptional results with quality, care, and professionalism.',
		'process_title' => 'Easy Steps to |Get Started',
		'process_steps' => "Find Us Online|Explore our website to discover our cleaning and property maintenance services.\nChoose Your Services|Select the services you need and request a free quote tailored to your requirements.\nBook Your Appointment|Confirm your booking, and our professional team will arrive at your scheduled time, ready to deliver reliable, high-quality service.",
		'achievements'  => "8K+|Services Provided\n12K+|Orders Completed\n300+|Team Members\n7K+|5-Star Reviews Received",
		'promise_title' => 'Choose Bright Nest Cleaning Co. for Your Cleaning Needs',
		'promise_text'  => "Maintaining a clean home or workplace is easier when you have a suitable cleaning plan and a reliable service provider. From everyday housekeeping and maid services to deep cleaning and commercial cleaning, choosing the right solution can help you maintain a more comfortable and hygienic environment.\n\nBright Nest Cleaning Co. provides a range of cleaning and property maintenance services designed to support residential and commercial customers. If you are searching for cleaning services in Dubai, contact our team to discuss your requirements, confirm service availability, and request a quotation tailored to your property.",
		'promise_close' => 'Get in touch with Bright Nest Cleaning Co. today to explore your cleaning options and arrange a service that fits your needs.',
		'numbers_title'   => 'Premium Cleaning & Maintenance Solutions for |Spotless Results',
		'reviews_eyebrow' => 'Testimonials',
		'reviews_title'   => 'What Our Customers |Say About Us',
	);
}

/**
 * Long homepage text (goes into Pages → Home, where it can be edited like any page).
 */
function brightnest_home_article() {
	return <<<'HTML'
<h2>Professional Cleaning Services in Dubai</h2>
<h3>Reliable Cleaning Services in Dubai</h3>
<p>Keeping your home or workplace clean and hygienic is essential for a comfortable and healthy lifestyle. However, busy work schedules, family responsibilities, and daily commitments can make it difficult to keep your home or workplace consistently clean. Professional cleaning services in Dubai offer a convenient solution for residents and businesses to get reliable, high-quality cleaning without the stress of doing it themselves.</p>
<p>Bright Nest Cleaning Co. provides professional cleaning and property maintenance solutions designed to meet the needs of residential and commercial customers. From routine home cleaning and hourly maid services to comprehensive deep cleaning and office cleaning, our team focuses on quality, attention to detail, and customer satisfaction. Whether you live in an apartment, own a villa, or manage a commercial property, we aim to make your space cleaner, fresher, and more comfortable.</p>
<p>Dubai is a vibrant city where residents and businesses often have demanding schedules. Finding enough time to clean every room, maintain bathrooms, sanitize kitchens, and keep workplaces organized can be challenging. Our professional cleaning solutions help simplify these responsibilities, allowing you to focus on your priorities while maintaining a pleasant living or working environment.</p>
<p>If you are looking for dependable cleaning services in Dubai, Bright Nest Cleaning Co. offers flexible solutions tailored to your property, schedule, and cleaning requirements.</p>

<h2>Why Choose Bright Nest Cleaning Co. for Cleaning Services in Dubai?</h2>
<p>Choosing the right cleaning company involves more than finding an affordable service. You need a team that understands your requirements, respects your property, follows appropriate cleaning practices, and delivers consistent results. Bright Nest Cleaning Co. aims to provide a convenient and professional experience for customers seeking residential and commercial cleaning services.</p>
<h3>1. Professional Experience and Attention to Detail</h3>
<p>Quality cleaning requires the right techniques, suitable equipment, and careful attention to detail. Our cleaning approach focuses on maintaining high standards across different areas of your property, including kitchens, bathrooms, bedrooms, living rooms, offices, and shared spaces.</p>
<p>Whether you require regular maintenance or a more thorough cleaning session, we tailor our approach to the condition of your property and your specific needs.</p>
<h3>2. A Comprehensive Range of Cleaning Services</h3>
<p>Every property has different cleaning requirements. Some customers need regular housekeeping, while others require deep cleaning, office cleaning, or assistance before moving into a new home.</p>
<p>Bright Nest Cleaning Co. offers a range of cleaning solutions to help customers manage these needs conveniently. Our services include:</p>
<ul>
<li><strong>Maid Services:</strong> Flexible housekeeping assistance for everyday cleaning tasks.</li>
<li><strong>House Cleaning:</strong> Routine cleaning for bedrooms, kitchens, bathrooms, and living areas.</li>
<li><strong>Deep Cleaning:</strong> Detailed cleaning for accumulated dirt, stubborn grime, and hard-to-reach areas.</li>
<li><strong>Office Cleaning:</strong> Professional cleaning for offices and commercial workspaces.</li>
<li><strong>Restaurant Cleaning:</strong> Cleaning solutions for dining areas and food-service environments.</li>
<li><strong>Industrial Cleaning:</strong> Cleaning for warehouses, workshops, and other industrial facilities.</li>
<li><strong>Move-In/Move-Out Cleaning:</strong> Detailed cleaning to prepare properties for new occupants or handovers.</li>
<li><strong>Pest Control:</strong> Solutions to help address common pest problems.</li>
<li><strong>Property Maintenance:</strong> Assistance with AC maintenance, electrical repairs, plumbing, painting, tile work, and general handyman services.</li>
</ul>
<p>Our aim is to make it easier for customers to find suitable cleaning and maintenance solutions through one service provider.</p>
<h3>3. Trained and Professional Cleaning Staff</h3>
<p>The quality of a cleaning service depends significantly on the people performing the work. A professional team should understand appropriate cleaning methods, handle household and workplace items carefully, and pay attention to areas that are often overlooked.</p>
<p>Bright Nest Cleaning Co. focuses on providing professional service and careful workmanship to help maintain clean, comfortable spaces. Whether you require a maid for routine household tasks or a team for a more extensive cleaning project, selecting the appropriate service helps ensure that your requirements are addressed effectively.</p>
<h3>4. Reliable Scheduling and Convenient Bookings</h3>
<p>Finding a cleaning service that fits your schedule can make property maintenance much easier. Whether you need a one-time cleaning appointment or recurring assistance, it is important to choose a service that accommodates your requirements.</p>
<p>Our booking process is designed to help customers communicate their cleaning needs, select suitable services, and arrange appointments conveniently. Clear communication about the property size, preferred date, and required tasks also helps ensure a smoother service experience.</p>
<h3>5. Transparent Pricing and Clear Communication</h3>
<p>Understanding the cost of cleaning services before booking helps you plan your budget and compare available options. Cleaning prices may depend on several factors, including property size, service duration, cleaning type, and the condition of the space.</p>
<p>Bright Nest Cleaning Co. encourages customers to discuss their requirements in advance so they can receive a quotation appropriate to the requested work. Confirming the service scope, duration, and any additional charges before booking can help avoid misunderstandings.</p>
<h3>6. Responsible Cleaning Practices</h3>
<p>Maintaining a clean environment is important for homes, offices, and commercial properties. Appropriate cleaning products and methods can help remove everyday dirt, dust, and grime from frequently used surfaces.</p>
<p>When selecting cleaning products, it is important to consider the surface being treated, the manufacturer's instructions, and the needs of children, pets, and other occupants. Bright Nest Cleaning Co. aims to provide cleaning solutions suited to the property's requirements, with appropriate attention to hygiene, product use, and customer preferences.</p>

<h3>Estimated Cost of Cleaning Services in Dubai</h3>
<p>The cost of hiring professional cleaners in Dubai can vary according to the service you choose and the size and condition of your property. Routine cleaning, deep cleaning, office cleaning, and specialized services may each have different pricing structures.</p>
<p>Hourly cleaning arrangements are often suitable for customers who need help with specific household tasks, while larger properties or more intensive cleaning projects may require additional time and resources.</p>
<p>The following factors can influence your cleaning quotation:</p>
<ul>
<li><strong>Property Size:</strong> Larger apartments, villas, and commercial spaces may require more cleaning time.</li>
<li><strong>Type of Cleaning:</strong> Routine housekeeping and detailed deep cleaning involve different levels of work.</li>
<li><strong>Service Duration:</strong> Hourly bookings and fixed-price projects may be calculated differently.</li>
<li><strong>Property Condition:</strong> Heavy dirt, accumulated grime, and neglected areas can require additional attention.</li>
<li><strong>Special Requirements:</strong> Interior windows, specific surfaces, and other requested tasks may affect the total cost.</li>
<li><strong>Frequency of Service:</strong> Regular cleaning arrangements may have different rates from one-time bookings.</li>
</ul>
<p>For an accurate estimate, share your property details and cleaning requirements with Bright Nest Cleaning Co. before confirming your appointment.</p>

<h3>Average Cost of Maid Services in Dubai</h3>
<p>Maid services are a convenient option for individuals, families, and professionals who need help maintaining their homes. Depending on your requirements, you may be able to arrange assistance for a few hours or explore recurring cleaning schedules.</p>
<p>The cost of maid services depends on the number of hours required, the frequency of visits, the tasks included, and the service provider's booking arrangements.</p>
<p>Before hiring a maid, consider the following:</p>
<ul>
<li><strong>Hourly Maid Services:</strong> Suitable for occasional cleaning or assistance with specific household tasks.</li>
<li><strong>Regular Maid Services:</strong> Appropriate for customers who prefer scheduled weekly or recurring cleaning.</li>
<li><strong>Monthly Arrangements:</strong> May suit households requiring consistent assistance, subject to the provider's available plans and terms.</li>
<li><strong>Task Requirements:</strong> Laundry, ironing, kitchen cleaning, and other household tasks should be confirmed in advance.</li>
<li><strong>Booking Terms:</strong> Check the service duration, cancellation policy, payment arrangements, and any additional charges.</li>
</ul>
<p>Bright Nest Cleaning Co. can help you identify a suitable cleaning arrangement based on your household needs and preferred schedule. Contact the team to discuss available options and request a quotation.</p>

<h2>How to Book Cleaning Services in Dubai</h2>
<p>Arranging a professional cleaning service can be straightforward when you know what to expect. Follow these simple steps to get started with Bright Nest Cleaning Co.</p>
<p><strong>Step 1: Explore Our Services</strong></p>
<p>Review the available cleaning and maintenance services to identify the option that best matches your requirements.</p>
<p><strong>Step 2: Share Your Requirements</strong></p>
<p>Provide details about your property, the areas that need cleaning, and any specific tasks you would like included.</p>
<p><strong>Step 3: Request a Quotation</strong></p>
<p>Ask for a quotation based on the service type, property size, and estimated time required.</p>
<p><strong>Step 4: Confirm Your Appointment</strong></p>
<p>Agree on the service details, pricing, and appointment schedule before confirming your booking.</p>
<p><strong>Step 5: Prepare for the Service</strong></p>
<p>Share any relevant access instructions or special considerations to help the cleaning team complete the work efficiently.</p>
HTML;
}

/**
 * Homepage title in Google / the browser tab.
 */
function brightnest_home_document_title( $title ) {
	if ( is_front_page() && '' !== trim( (string) brightnest_opt( 'home_seo_title' ) ) ) {
		return brightnest_opt( 'home_seo_title' );
	}
	return $title;
}
add_filter( 'pre_get_document_title', 'brightnest_home_document_title' );

/**
 * One time: put the homepage copy in place (older texts replaced, empty Home page filled in).
 */
function brightnest_home_seed() {
	if ( get_option( 'bn_home_copy_v2' ) ) {
		return;
	}
	update_option( 'bn_home_copy_v2', 1 );
	foreach ( brightnest_home_replaced() as $key => $val ) {
		set_theme_mod( 'bn_' . $key, $val );
	}
	$front = (int) get_option( 'page_on_front' );
	if ( $front && '' === trim( (string) get_post_field( 'post_content', $front ) ) ) {
		wp_update_post( array( 'ID' => $front, 'post_content' => brightnest_home_article() ) );
	}
}
add_action( 'init', 'brightnest_home_seed', 30 );

/**
 * One time: Arabic text for the offer pop-up (only where none was typed in yet).
 */
function brightnest_offer_ar_seed() {
	if ( get_option( 'bn_offer_ar_v1' ) ) {
		return;
	}
	update_option( 'bn_offer_ar_v1', 1 );
	$ar = array(
		'offer_badge'     => '20%',
		'offer_badge_sub' => 'خصم',
		'offer_title'     => 'وفّر على خدمتك الأولى',
		'offer_text'      => 'استمتع بخصم خاص على خدمات التنظيف ومكافحة الحشرات والصيانة في جميع أنحاء الإمارات.',
		'offer_btn'       => 'احصل على الخصم',
	);
	foreach ( $ar as $key => $val ) {
		if ( '' === trim( (string) get_theme_mod( 'bn_' . $key . '_ar', '' ) ) ) {
			set_theme_mod( 'bn_' . $key . '_ar', $val );
		}
	}
}
add_action( 'init', 'brightnest_offer_ar_seed', 31 );

<?php

return [
  0 => [
    "htmlType" => "input",
    "width" => 4,
    "name" => "first_name",
    "label" => "Name",
    "placeholder" => "John",
    "required" => true,
    "type" => "text",
    "maxLength" => 191
  ],
  1 => [
    "htmlType" => "input",
    "width" => 4,
    "name" => "last_name",
    "label" => "Last Name",
    "placeholder" => "Smith",
    "required" => false,
    "type" => "text",
    "maxLength" => 191
  ],
  2 => [
    "htmlType" => "input",
    "width" => 4,
    "name" => "email",
    "label" => "Email",
    "placeholder" => "johnSmith@coolMail.com",
    "required" => true,
    "type" => "email",
    "maxLength" => 191
  ],
  3 => [
    "htmlType" => "input",
    "width" => 4,
    "name" => "phone_number_cell",
    "label" => "Phone Number",
    "placeholder" => "123-456-789",
    "required" => false,
    "type" => "tel",
    "maxLength" => 191
  ],
  4 => [
    "htmlType" => "input",
    "width" => 4,
    "name" => "customer_address",
    "label" => "Address",
    "placeholder" => "123 Awesome Ln",
    "required" => false,
    "type" => "text",
    "maxLength" => 191
  ],
  5 => [
    "htmlType" => "input",
    "width" => 4,
    "name" => "customer_city",
    "label" => "City",
    "placeholder" => "Cityville",
    "required" => false,
    "type" => "text",
    "maxLength" => 191
  ],
  6 => [
    "htmlType" => "input",
    "width" => 4,
    "name" => "customer_state",
    "label" => "State",
    "placeholder" => "Oregon",
    "required" => false,
    "type" => "text",
    "maxLength" => 255
  ],
  7 => [
    "htmlType" => "input",
    "width" => 4,
    "name" => "customer_zip",
    "label" => "Zip",
    "placeholder" => "123456",
    "required" => false,
    "type" => "text",
    "maxLength" => 191
  ],
  8 => [
    "htmlType" => "input",
    "width" => 4,
    "name" => "requested_date",
    "label" => "Requested Date",
    "placeholder" => "MM/DD/YYYY",
    "required" => false,
    "type" => "date",
    "maxLength" => 191,
  ],
  9 => [
    "htmlType" => "textarea",
    "width" => 12,
    "name" => "comments",
    "label" => "Comments",
    "placeholder" => "I need...",
    "required" => false,
    "type" => "text",
    "maxLength" => 10000,
  ],
  10 => [
    "htmlType" => "textarea",
    "width" => 12,
    "name" => "comments",
    "label" => "Comments",
    "placeholder" => "Your comments/questions...",
    "required" => false,
    "type" => "text",
    "maxLength" => 10000,
  ],
  11 => [
    "htmlType" => "input",
    "width" => 4,
    "name" => "phone_number_cell",
    "label" => "Cell Phone Number",
    "placeholder" => "123-456-789",
    "required" => true,
    "type" => "tel",
    "maxLength" => 191
  ],
  12 => [
    "htmlType" => "textarea",
    "width" => 12,
    "name" => "comments",
    "label" => "Comments",
    "placeholder" => "I'd like more information about...",
    "required" => false,
    "type" => "text",
    "maxLength" => 10000,
  ],
  13 => [
    "htmlType" => "input",
    "width" => 4,
    "name" => "phone_number_cell",
    "label" => "Phone Number",
    "placeholder" => "123-456-789",
    "required" => true,
    "type" => "tel",
    "maxLength" => 191
  ],
  14 => [
    "htmlType" => "textarea",
    "width" => 12,
    "name" => "comments",
    "label" => "Comments",
    "placeholder" => "Additional details...",
    "required" => false,
    "type" => "text",
    "maxLength" => 500,
  ],
  15 => [
    "htmlType" => "input",
    "width" => 4,
    "name" => "requested_time",
    "label" => "Requested Time",
    "required" => false,
    "type" => "time",
    "maxLength" => 500,
    "step" => 60,
  ],
  16 => [
    "htmlType" => "input",
    "width" => 4,
    "name" => "newsletter",
    "label" => "Sign up for our newsletter",
    "required" => false,
    "type" => "checkbox",
    "maxLength" => 100,
  ],
  17 => [
    "isCustomImageUploader" => true,
    'name' => 'custom_file_uploader',
    'hiddenFieldSlug' => 'cdn_url_1',
  ],
  18 => [
    "htmlType" => "textarea",
    "width" => 12,
    "name" => "comments",
    "label" => "How Can We Help?",
    "placeholder" => "Your comments/questions...",
    "required" => false,
    "type" => "text",
    "maxLength" => 10000,
  ],
  19 => [
    "width" => 12,
    "name" => "customer_location_id",
    "label" => "Preferred Location",
    "maxLength" => 50,
    "isDropdown" => true,
    "options" => [],
  ],
  20 => [
    "htmlType" => "input",
    "width" => 4,
    "name" => "parts_brand",
    "label" => "Brand",
    "placeholder" => "Hot Tub Brand",
    "required" => true,
    "type" => "text",
    "maxLength" => 191,
    "isCustomField" => true,
  ],
  21 => [
    "htmlType" => "input",
    "width" => 4,
    "name" => "parts_model",
    "label" => "Model",
    "placeholder" => "Model XYZ",
    "required" => true,
    "type" => "text",
    "maxLength" => 191,
    "isCustomField" => true,
  ],
  22 => [
    "htmlType" => "input",
    "width" => 4,
    "name" => "parts_year",
    "label" => "Year",
    "placeholder" => "2000",
    "required" => true,
    "type" => "number",
    "maxLength" => 191,
    "isCustomField" => true,
  ],
  23 => [
    "htmlType" => "textarea",
    "width" => 12,
    "name" => "comments",
    "label" => "Info About Part Needed",
    "placeholder" => "Part# 123456 cracked and I need a new one installed...",
    "required" => true,
    "type" => "text",
    "maxLength" => 10000,
  ],
  24 => [
    "htmlType" => "input",
    "width" => 4,
    "name" => "trade_in_year",
    "label" => "Year",
    "placeholder" => "2000",
    "required" => true,
    "type" => "number",
    "maxLength" => 191,
    "isCustomField" => true,
  ],
  25 => [
    "htmlType" => "input",
    "width" => 4,
    "name" => "trade_in_brand",
    "label" => "Brand",
    "placeholder" => "Hot Tub Brand",
    "required" => true,
    "type" => "text",
    "maxLength" => 191,
    "isCustomField" => true,
  ],
  26 => [
    "htmlType" => "input",
    "width" => 4,
    "name" => "trade_in_model",
    "label" => "Model",
    "placeholder" => "Model XYZ",
    "required" => true,
    "type" => "text",
    "maxLength" => 191,
    "isCustomField" => true,
  ],
  27 => [
    "width" => 12,
    "name" => "trade_in_intent",
    "label" => "Which would you prefer?",
    "required" => false,
    "radio" => true,
    "isCustomField" => true,
    "maxLength" => 191,
    "options" => [
      [
        "key" =>"trading",
        "value" => "I am interested In trading",
        "name" => "trade_in_intent"
      ],
      [
        "key" =>"selling",
        "value" => "I am interested in selling only",
        "name" => "trade_in_intent"
      ],
      [
        "key" =>"trade_or_sell",
        "value" => "I am open to either trade or sell",
        "name" => "trade_in_intent"
      ],
      [
        "key" =>"not_sure",
        "value" => "I am not sure yet",
        "name" => "trade_in_intent"
      ],
    ],
  ],
  28 => [
    "width" => 12,
    "name" => "contact_preference",
    "label" => "How would you like to be contacted?",
    "required" => false,
    "radio" => true,
    "isCustomField" => true,
    "maxLength" => 191,
    "options" => [
      [
        "key" =>"email",
        "value" => "Email",
        "name" => "contact_preference"
      ],
      [
        "key" =>"text_message",
        "value" => "Text Message",
        "name" => "contact_preference"
      ],
      [
        "key" =>"phone_call",
        "value" => "Phone Call",
        "name" => "contact_preference"
      ],
      [
        "key" =>"no_preference",
        "value" => "I have no preference",
        "name" => "contact_preference"
      ],
    ],
  ],
  29 => [
    "width" => 12,
    "name" => "review_rating",
    "label" => "Please rate your experience",
    "required" => false,
    "radio" => true,
    "isCustomField" => true,
    "maxLength" => 191,
    "options" => [
      [
        "key" =>"1",
        "value" => "1 Star (Poor)",
        "name" => "review_rating"
      ],
      [
        "key" =>"2",
        "value" => "2 Stars",
        "name" => "review_rating"
      ],
      [
        "key" =>"3",
        "value" => "3 Stars (Average)",
        "name" => "review_rating"
      ],
      [
        "key" =>"4",
        "value" => "4 Stars",
        "name" => "review_rating"
      ],
      [
        "key" =>"5",
        "value" => "5 Stars (Great)",
        "name" => "review_rating"
      ],
    ],
  ],
  30 => [
    "htmlType" => "input",
    "width" => 4,
    "name" => "new_customer_city",
    "label" => "New City",
    "placeholder" => "Cityville",
    "required" => false,
    "type" => "text",
    "isCustomField" => true,
    "maxLength" => 191
  ],
  31 => [
    "htmlType" => "input",
    "width" => 4,
    "name" => "new_customer_state",
    "label" => "New State",
    "placeholder" => "Oregon",
    "required" => false,
    "type" => "text",
    "isCustomField" => true,
    "maxLength" => 255
  ],
  32 => [
    "htmlType" => "input",
    "width" => 4,
    "name" => "new_customer_zip",
    "label" => "New Zip",
    "placeholder" => "123456",
    "required" => false,
    "type" => "text",
    "isCustomField" => true,
    "maxLength" => 191
  ],
  33 => [
    "width" => 12,
    "name" => "contact_preference",
    "label" => "How would you like your free quote sent?",
    "required" => false,
    "radio" => true,
    "isCustomField" => true,
    "maxLength" => 191,
    "options" => [
      [
        "key" =>"email",
        "value" => "Email",
        "name" => "contact_preference"
      ],
      [
        "key" =>"text_message",
        "value" => "Text Message",
        "name" => "contact_preference"
      ],
      [
        "key" =>"phone_call",
        "value" => "Phone Call",
        "name" => "contact_preference"
      ],
      [
        "key" =>"no_preference",
        "value" => "I have no preference",
        "name" => "contact_preference"
      ],
    ],
  ],
  34 => [
    "width" => 12,
    "name" => "review_rating",
    "label" => "Please rate your experience",
    "required" => false,
    "radio" => true,
    "isCustomField" => true,
    "maxLength" => 191,
    "options" => [
      [
        "key" =>"1",
        "value" => "1 Star (Poor)",
        "name" => "review_rating"
      ],
      [
        "key" =>"2",
        "value" => "2 Stars",
        "name" => "review_rating"
      ],
      [
        "key" =>"3",
        "value" => "3 Stars (Average)",
        "name" => "review_rating"
      ],
      [
        "key" =>"4",
        "value" => "4 Stars",
        "name" => "review_rating"
      ],
      [
        "key" =>"5",
        "value" => "5 Stars (Great)",
        "name" => "review_rating"
      ],
    ],
  ],
  35 => [
    "htmlType" => "input",
    "width" => 12,
    "name" => "position",
    "label" => "What Position Are You Applying For?",
    "maxLength" => 50,
    "options" => [
        [
          "key" =>"Sales Associate",
          "value" => "Sales Associate"
        ],
        [
          "key" =>"Counter Assistant/Sales Trainee",
          "value" => "Counter Assistant/Sales Trainee"
        ],
        [
          "key" =>"Delivery Driver/Service Tech",
          "value" => "Delivery Driver/Service Tech"
        ],
    ],
    "isCustomField" => true,
  ],
  36 => [
    "htmlType" => "input",
    "width" => 12,
    "name" => "employment_type",
    "label" => "Desired Employment Type",
    "maxLength" => 50,
    "options" => [
        [
          "key" =>"Full-Time",
          "value" => "Full-Time"
        ],
        [
          "key" =>"Part-Time",
          "value" => "Part-Time"
        ],
        [
          "key" =>"Full or Part Time",
          "value" => "Full or Part Time"
        ],
    ],
    "isCustomField" => true,
  ],
  37 => [
    "isCustomDocumentUploader" => true,
    "label" => "Upload Cover Letter",
    'name' => 'custom_file_uploader',
    'hiddenFieldSlug' => 'cdn_url_1',
  ],
  38 => [
    "isCustomDocumentUploader" => true,
    "label" => "Upload Resume",
    'name' => 'custom_file_uploader',
    'hiddenFieldSlug' => 'cdn_url_2',
  ],
  39 => [
    "width" => 12,
    "name" => "products_customer_interested_in",
    "label" => "Are you interested in a Swim Spa or Hot Tub?",
    "required" => false,
    "radio" => true,
    "isCustomField" => true,
    "maxLength" => 191,
    "options" => [
      [
        "key" =>"swim-spa",
        "value" => "Swim Spa",
        "name" => "products_customer_interested_in"
      ],
      [
        "key" =>"hot-tub",
        "value" => "Hot Tub",
        "name" => "products_customer_interested_in"
      ],
      [
        "key" =>"neither",
        "value" => "Neither",
        "name" => "products_customer_interested_in"
      ],
    ],
  ],
  40 => [
    "width" => 12,
    "name" => "interested_in_financing",
    "label" => "Are you interested in financing?",
    "required" => false,
    "radio" => true,
    "isCustomField" => true,
    "maxLength" => 191,
    "options" => [
      [
        "key" =>"no",
        "value" => "No",
        "name" => "interested_in_financing"
      ],
      [
        "key" =>"yes",
        "value" => "Yes",
        "name" => "interested_in_financing"
      ],
    ],
  ],
  41 => [
    "htmlType" => "input",
    "width" => 4,
    "isCustomField" => true,
    "name" => "friends_phone_number",
    "label" => "Friend's Phone Number",
    "placeholder" => "123-456-789",
    "required" => true,
    "type" => "tel",
    "maxLength" => 191
  ],
  42 => [
    "htmlType" => "input",
    "width" => 4,
    "isCustomField" => true,
    "name" => "friends_first_name",
    "label" => "Friend's First Name",
    "placeholder" => "John",
    "required" => true,
    "type" => "text",
    "maxLength" => 191
  ],
  43 => [
    "htmlType" => "input",
    "width" => 4,
    "isCustomField" => true,
    "name" => "friends_last_name",
    "label" => "Friend's Last Name",
    "placeholder" => "Smith",
    "required" => false,
    "type" => "text",
    "maxLength" => 191
  ],
  44 => [
    "htmlType" => "input",
    "width" => 4,
    "isCustomField" => true,
    "name" => "friends_email",
    "label" => "Friend's Email",
    "placeholder" => "johnSmith@coolMail.com",
    "required" => true,
    "type" => "email",
    "maxLength" => 191
  ],
  45 => [
    "htmlType" => "input",
    "width" => 4,
    "name" => "new_customer_address",
    "label" => "New Address",
    "placeholder" => "123 Awesome Ln",
    "required" => false,
    "type" => "text",
    "isCustomField" => true,
    "maxLength" => 191
  ],
  46 => [
    "htmlType" => "textarea",
    "width" => 12,
    "name" => "comments",
    "label" => "Comments",
    "placeholder" => "Please share any issues or suggestions for improvement...",
    "required" => false,
    "type" => "text",
    "maxLength" => 10000,
  ],
  47 => [
    "width" => 6,
    "name" => "product_type",
    "label" => "Pool Type",
    "required" => false,
    "isDropdown" => true,
    "isCustomField" => true,
    "maxLength" => 191,
    "options" => [
      [
        "key" =>"in-ground-pool",
        "value" => "In-ground Pool",
        "name" => "product_type"
      ],
      [
        "key" =>"wood-pool-on-ground",
        "value" => "Wood Pool On-ground",
        "name" => "product_type"
      ],
      [
        "key" =>"steel-pool-on-ground",
        "value" => "Steel Pool On-ground",
        "name" => "product_type"
      ],
      [
        "key" =>"above-ground",
        "value" => "Above Ground",
        "name" => "product_type"
      ],
    ],
  ],
  48 => [
    "htmlType" => "textarea",
    "width" => 12,
    "name" => "comments",
    "label" => "Comments / Pool Size",
    "placeholder" => "",
    "required" => false,
    "type" => "text",
    "maxLength" => 10000,
  ],
  49 => [
    "width" => 6,
    "name" => "topic",
    "label" => "Needs or problems",
    "required" => false,
    "isDropdown" => true,
    "isCustomField" => true,
    "maxLength" => 191,
    "options" => [
      [
        "key" =>"pool-opening",
        "value" => "Pool Opening",
        "name" => "topic"
      ],
      [
        "key" =>"pool-closing",
        "value" => "Pool Closing",
        "name" => "topic"
      ],
      [
        "key" =>"leak",
        "value" => "Leak",
        "name" => "topic"
      ],
      [
        "key" =>"liner-replacement",
        "value" => "Liner Replacement",
        "name" => "topic"
      ],
      [
        "key" =>"filtration-issue",
        "value" => "Filtration Issue",
        "name" => "topic"
      ],
      [
        "key" =>"pump-problem",
        "value" => "Pump Problem",
        "name" => "topic"
      ],
      [
        "key" =>"plumbing",
        "value" => "Plumbing",
        "name" => "topic"
      ],
      [
        "key" =>"service-call",
        "value" => "Service Call",
        "name" => "topic"
      ],
      [
        "key" =>"other",
        "value" => "Other",
        "name" => "topic"
      ],
    ],
  ],
  50 => [
    "width" => 6,
    "name" => "product_type",
    "label" => "Hot Tub / Swim Spa Type",
    "required" => false,
    "isDropdown" => true,
    "isCustomField" => true,
    "maxLength" => 191,
    "options" => [
      [
        "key" =>"1-pump",
        "value" => "1 Pump",
        "name" => "product_type"
      ],
      [
        "key" =>"2-pump",
        "value" => "2 Pump",
        "name" => "product_type"
      ],
      [
        "key" =>"3-pump",
        "value" => "3 Pump",
        "name" => "product_type"
      ],
    ],
  ],
  51 => [
    "width" => 6,
    "name" => "topic",
    "label" => "Needs or Problems",
    "required" => true,
    "isDropdown" => true,
    "isCustomField" => true,
    "maxLength" => 191,
    "options" => [
      [
        "key" =>"Blower not working",
        "value" => "Blower not working",
        "name" => "topic"
      ],
      [
        "key" =>"Cover Lifter",
        "value" => "Cover Lifter",
        "name" => "topic"
      ],
      [
        "key" =>"GFCI tripping / no power",
        "value" => "GFCI tripping / no power",
        "name" => "topic"
      ],
      [
        "key" =>"Jets not working",
        "value" => "Jets not working",
        "name" => "topic"
      ],
      [
        "key" =>"Leaking",
        "value" => "Leaking",
        "name" => "topic"
      ],
      [
        "key" =>"Lights not working",
        "value" => "Lights not working",
        "name" => "topic"
      ],
      [
        "key" =>"Multiple Issues",
        "value" => "Multiple Issues",
        "name" => "topic"
      ],
      [
        "key" =>"Pump not working",
        "value" => "Pump not working",
        "name" => "topic"
      ],
      [
        "key" =>"FLO error code",
        "value" => "FLO error code",
        "name" => "topic"
      ],
      [
        "key" =>"Spa closing",
        "value" => "Spa closing",
        "name" => "topic"
      ],
      [
        "key" =>"Spa opening",
        "value" => "Spa opening",
        "name" => "topic"
      ],
      [
        "key" =>"Topside controls",
        "value" => "Topside controls",
        "name" => "topic"
      ],
      [
        "key" =>"Hot-Tub move",
        "value" => "Hot-Tub move",
        "name" => "topic"
      ],
      [
        "key" =>"Hot-Tub overheating",
        "value" => "Hot-Tub overheating",
        "name" => "topic"
      ],
      [
        "key" =>"Parts",
        "value" => "Parts",
        "name" => "topic"
      ],
      [
        "key" =>"Error",
        "value" => "Error",
        "name" => "topic"
      ],
      [
        "key" =>"Spa maintenance",
        "value" => "Spa maintenance",
        "name" => "topic"
      ],
    ],
  ],
  52 => [
    "width" => 6,
    "name" => "year",
    "label" => "Hot Tub / Swim Spa age",
    "required" => false,
    "isDropdown" => true,
    "isCustomField" => true,
    "maxLength" => 191,
    "options" => [
      [
        "key" =>"0-1 Years",
        "value" => "0-1 Years",
        "name" => "year"
      ],
      [
        "key" =>"2-3-years",
        "value" => "2-3 Years",
        "name" => "year"
      ],
      [
        "key" =>"4-5-years",
        "value" => "4-5 Years",
        "name" => "year"
      ],
      [
        "key" =>"5+-years",
        "value" => "5+ Years",
        "name" => "year"
      ],
    ],
  ],
  53 => [
    "htmlType" => "textarea",
    "width" => 12,
    "name" => "comments",
    "label" => "Brand and Comments",
    "placeholder" => "",
    "required" => false,
    "type" => "text",
    "maxLength" => 10000,
  ],
  54 => [
    "htmlType" => "textarea",
    "width" => 12,
    "name" => "comments",
    "label" => "Comments",
    "placeholder" => "Your comments/questions...",
    "required" => true,
    "type" => "text",
    "maxLength" => 10000,
  ],
  55 => [
    "htmlType" => "input",
    "width" => 4,
    "name" => "requested_color",
    "label" => "Requested Color",
    "placeholder" => "Navy Blue",
    "required" => true,
    "type" => "text",
    "maxLength" => 191
  ],
 56 => [
    "htmlType" => "input",
    "width" => 4,
    "name" => "skirt_length_inches",
    "label" => "Skirt Length (in inches)",
    "required" => true,
    "type" => "number",
    "maxLength" => 191,
    "isCustomField" => true,
  ],
  57 => [
    "htmlType" => "input",
    "width" => 4,
    "name" => "radius_corners",
    "label" => "Radius Corners (if applicable)",
    "type" => "number",
    "maxLength" => 191,
    "isCustomField" => true,
  ],
  58 => [
    "isCustomDocumentUploader" => true,
    'name' => 'custom_file_uploader',
    'hiddenFieldSlug' => 'cdn_url_1',
    "label" => "Upload photo of your spa...",
  ],
  59 => [
    "htmlType" => "textarea",
    "width" => 12,
    "name" => "comments",
    "label" => "Special Requests",
    "required" => false,
    "type" => "text",
    "maxLength" => 10000,
  ],
  60 => [
    "isCustomDocumentUploader" => true,
    'name' => 'custom_file_uploader',
    'hiddenFieldSlug' => 'cdn_url_1',
    "label" => "Upload photo of your spa...",
  ],
  61 => [
      "width" => 12,
      "isHTMLBlock" => true,
      "html" => "<div class='form_message_disclaimer sms_consent_disclaimer reactive'><p>By providing your phone number, you <strong>consent to receive text messages</strong> from Vienna Hot Tubs regarding your inquiry, appointments, service updates, quotes, and other relevant information. <strong>Message frequency may vary. Message and data rates may apply.</strong> For assistance, reply <strong>HELP</strong> or contact our Sales team at <a href='tel:7037340077'>703-734-0077</a>, Service team at <a href='tel:7034290029'>703-429-0029</a>, or email <a href='mailto:info@viennahottubs-swimspas-saunas.com'>info@viennahottubs-swimspas-saunas.com</a>. To stop receiving messages, <strong>reply STOP</strong>. <strong>No further messages will be sent unless you opt in again.</strong> For details, see our <a href='/privacy-policy/'>Privacy Policy</a> and <a href='/terms-and-conditions/'>Terms &amp; Conditions</a>.</p></div>",
  ],
];

<?php
// seed_instructional_courses.php
// Seeds 8 Instructional Course Assessments (Pre-Test and Post-Test for Grades 7, 8, 9, 10)

require_once __DIR__ . '/config.php';

$assessments = [
    // ==========================================
    // GRADE 7 - PRE-TEST (Instructional)
    // ==========================================
    [
        'title' => 'GRADE 7 - PRE-TEST - The Power of Renewable Energy',
        'test_type' => 'Pre-Test',
        'target_profile' => 'Instructional',
        'target_grade' => 7,
        'status' => 'Published',
        'passage_text' => '<p>When strong typhoons struck the island of Isla Verde, the community often suffered from weeks of power outages. The local fishing cooperative relied on costly, noisy diesel generators that emitted thick black smoke and frequently ran out of fuel during prolonged storms. Without electricity, children struggled to study at night, and fish catches could not be stored in freezers.</p><p>To solve this ongoing crisis, a youth council partnered with clean energy engineers to install a community solar micro-grid. They mounted durable solar panels on the roof of the multi-purpose civic hall and connected them to rechargeable lithium batteries. In addition, small solar-powered street lamps were placed along the shoreline where fishermen docked their wooden boats.</p><p>The transformation was immediate. Families saved money previously spent on expensive diesel and kerosene. The cooperative established a communal cold storage unit powered entirely by sunlight, allowing fishermen to preserve their catch and sell at fair market prices. Most importantly, students gathered at the lighted community hall each evening to complete their school assignments safely.</p><p>The success of the Isla Verde project proved that remote rural communities do not need to wait for large commercial power plants to enjoy reliable electricity. Through collective action and sustainable technology, small islands can build clean and resilient power systems on their own.</p>',
        'questions' => [
            [
                'type' => 'multichoice',
                'question' => 'What caused frequent power outages on the island of Isla Verde?',
                'options' => ['Strong seasonal typhoons', 'Earthquakes and landslides', 'Volcanic eruptions', 'Severe prolonged droughts'],
                'correct' => 0
            ],
            [
                'type' => 'multichoice',
                'question' => 'What source of power did the fishing cooperative rely on before the project?',
                'options' => ['A geothermal power plant', 'Costly and noisy diesel generators', 'Hydroelectric water wheels', 'A commercial coal plant'],
                'correct' => 1
            ],
            [
                'type' => 'multichoice',
                'question' => 'What was a major disadvantage of using the diesel generators?',
                'options' => ['They were too quiet to notice', 'They emitted thick smoke and often ran out of fuel', 'They required sunlight to run', 'They were completely free to operate'],
                'correct' => 1
            ],
            [
                'type' => 'multichoice',
                'question' => 'Where were the new solar panels mounted?',
                'options' => ['On individual fishing boats', 'On the roof of the multi-purpose civic hall', 'On floating rafts out at sea', 'Along the sandy beach shoreline'],
                'correct' => 1
            ],
            [
                'type' => 'multichoice',
                'question' => 'How did the solar micro-grid help the fishermen preserve their livelihood?',
                'options' => ['It powered a communal cold storage unit for fish catches', 'It built larger motorized fishing boats', 'It provided free diesel fuel for boat engines', 'It cleaned the saltwater in the bay'],
                'correct' => 0
            ],
            [
                'type' => 'multichoice',
                'question' => 'Where did students gather each evening to study safely?',
                'options' => ['At the lighted community hall', 'Inside dark private homes', 'On the docking pier', 'At the local market stalls'],
                'correct' => 0
            ],
            [
                'type' => 'multichoice',
                'question' => 'What financial benefit did island families experience after the solar installation?',
                'options' => ['They received cash payments from the city', 'They saved money previously spent on diesel and kerosene', 'They stopped buying food and supplies', 'They sold electricity to neighboring islands'],
                'correct' => 1
            ],
            [
                'type' => 'multichoice',
                'question' => 'Which word best describes the Isla Verde clean energy initiative?',
                'options' => ['Wasteful', 'Short-lived', 'Sustainable', 'Impractical'],
                'correct' => 2
            ],
            [
                'type' => 'multichoice',
                'question' => 'What is the author\'s main purpose in writing this passage?',
                'options' => ['To describe how to repair a diesel generator', 'To illustrate how community cooperation and clean energy can solve local crises', 'To discourage people from living on remote islands', 'To explain the scientific physics of solar cells'],
                'correct' => 1
            ],
            [
                'type' => 'multichoice',
                'question' => 'What conclusion can be drawn from the success of the Isla Verde project?',
                'options' => ['Small communities can build clean and resilient power systems through collective action', 'Islands must always rely on mainland power grids', 'Diesel fuel is more practical than solar energy', 'Solar power works only during clear summer months'],
                'correct' => 0
            ]
        ]
    ],

    // ==========================================
    // GRADE 7 - POST-TEST (Instructional)
    // ==========================================
    [
        'title' => 'GRADE 7 - POST-TEST - Protecting Mangrove Ecosystems',
        'test_type' => 'Post-Test',
        'target_profile' => 'Instructional',
        'target_grade' => 7,
        'status' => 'Published',
        'passage_text' => '<p>Along the coastal shores of Oriental Mindoro, thick mangrove forests once formed a green shield between the stormy sea and coastal villages. Over the past decades, large areas of these vital wetlands were cleared for fishpond development and firewood gathering. When severe tropical storms swept across the coast, villages without mangrove buffers experienced violent wave surges, severe shoreline erosion, and heavy flooding.</p><p>Recognizing the danger, local high school students and community elders launched a coastal rehabilitation project called \'Bakawan Buhay.\' Every weekend, volunteers gathered propagules, the slender seedling pods dropped by mature mangrove trees, and nurtured them in temporary nurseries. Once the seedlings developed sturdy root systems, teams waded into the muddy intertidal mudflats to plant them in alternating rows.</p><p>Mangrove trees possess intricate, tangled root systems that perform several essential ecological functions. Above the water, their roots dissipate strong wave energy, reducing surge height before water reaches populated settlements. Beneath the water, the sheltered root mazes provide vital feeding and breeding grounds for juvenile fish, crabs, and shrimp.</p><p>Within three years, the newly planted mangroves matured into a dense coastal wetland. Local fishermen reported higher daily catches of crabs and fish, while storm surges during the monsoon season caused minimal shoreline damage. The community learned that protecting natural coastal defenses is far more effective and affordable than building concrete seawalls.</p>',
        'questions' => [
            [
                'type' => 'multichoice',
                'question' => 'Why were the coastal mangrove forests cleared in previous decades?',
                'options' => ['For fishpond development and firewood gathering', 'To make room for deep-water ports', 'To construct coastal tourist resorts', 'To build large concrete airports'],
                'correct' => 0
            ],
            [
                'type' => 'multichoice',
                'question' => 'What happened to coastal villages after the mangrove trees were removed?',
                'options' => ['They experienced violent wave surges and severe erosion', 'Their fish catches increased dramatically', 'They became cooler and less humid', 'Rainfall stopped completely along the coast'],
                'correct' => 0
            ],
            [
                'type' => 'multichoice',
                'question' => 'What was the official name of the community rehabilitation project?',
                'options' => ['Luntiang Baybayin', 'Bakawan Buhay', 'Sagip Karagatan', 'Bantay Dagat'],
                'correct' => 1
            ],
            [
                'type' => 'multichoice',
                'question' => 'What are \'propagules\' as described in the reading passage?',
                'options' => ['Heavy wooden stakes used to anchor boats', 'Slender seedling pods dropped by mature mangrove trees', 'Chemical fertilizers used in commercial fishponds', 'Artificial concrete blocks used as wave barriers'],
                'correct' => 1
            ],
            [
                'type' => 'multichoice',
                'question' => 'How do mangrove roots protect coastal villages above the water line?',
                'options' => ['They catch rainwater for drinking', 'They dissipate strong wave energy and reduce surge height', 'They block wind from reaching houses', 'They reflect sunlight back into the atmosphere'],
                'correct' => 1
            ],
            [
                'type' => 'multichoice',
                'question' => 'What ecological role do underwater mangrove roots provide for marine wildlife?',
                'options' => ['They trap predatory sharks', 'They serve as feeding and breeding grounds for young fish, crabs, and shrimp', 'They absorb saltwater to create freshwater streams', 'They eliminate marine bacteria'],
                'correct' => 1
            ],
            [
                'type' => 'multichoice',
                'question' => 'How long did it take for the planted seedlings to mature into a dense wetland?',
                'options' => ['Six months', 'One full year', 'Three years', 'Ten years'],
                'correct' => 2
            ],
            [
                'type' => 'multichoice',
                'question' => 'What economic improvement did local fishermen report after the mangroves matured?',
                'options' => ['Higher daily catches of crabs and fish', 'Cheaper boat engine repairs', 'Higher fuel prices', 'Lower cost of fishing nets'],
                'correct' => 0
            ],
            [
                'type' => 'multichoice',
                'question' => 'What major lesson did the community learn from the Bakawan Buhay project?',
                'options' => ['Concrete seawalls are always superior to trees', 'Natural coastal defenses are more effective and affordable than artificial seawalls', 'Mangroves should be harvested regularly for firewood', 'Coastal restoration requires expensive foreign machinery'],
                'correct' => 1
            ],
            [
                'type' => 'multichoice',
                'question' => 'What is the main idea of the entire passage?',
                'options' => ['Mangrove timber makes high-quality charcoal', 'Typhoons cannot be prevented in the Philippines', 'Community-led mangrove restoration protects coastlines and restores marine biodiversity', 'Commercial fishponds are necessary for national food supply'],
                'correct' => 2
            ]
        ]
    ],

    // ==========================================
    // GRADE 8 - PRE-TEST (Instructional)
    // ==========================================
    [
        'title' => 'GRADE 8 - PRE-TEST - Indigenous Engineering: The Rice Terraces',
        'test_type' => 'Pre-Test',
        'target_profile' => 'Instructional',
        'target_grade' => 8,
        'status' => 'Published',
        'passage_text' => '<p>Carved into the steep mountain slopes of Ifugao over two thousand years ago, the Rice Terraces stand as a monumental achievement in indigenous engineering. Built entirely by hand using primitive stone and wooden tools, these stepping-stone agricultural fields rise thousands of feet into the Cordillera mountain range. Ancient builders moved millions of tons of earth and rock without modern machinery to create productive farmland on terrain once thought impossible to cultivate.</p><p>The genius of the rice terraces lies in their sophisticated hydraulic design. Water originates from high-altitude rainforests, which act as natural sponges absorbing heavy tropical rainfall. Ancient engineers constructed a network of hand-carved stone canals, bamboo conduits, and sluice gates that channeled mountain runoff evenly from the highest tier down to the lowest terrace. This gravity-fed system ensures that every parcel of land receives adequate water without causing catastrophic landslides or soil erosion.</p><p>Furthermore, terrace maintenance relies on an indigenous system of cooperative labor known as \'ubbu.\' When retaining walls collapse after torrential typhoons, farming clans pool their labor to gather river stones and rebuild damaged terraces without exchanging money. Traditional pest management and water rotation schedules are regulated by village elders in accordance with seasonal astronomical observations.</p><p>Today, the terraces face serious challenges, including climate-induced droughts and younger generations migrating to urban commercial centers. Preserving this living cultural landscape requires not only physical repair but also honoring the deep indigenous knowledge systems that harmonize food production with ecological preservation.</p>',
        'questions' => [
            [
                'type' => 'multichoice',
                'question' => 'Where are the ancient Rice Terraces located?',
                'options' => ['On the plains of Central Luzon', 'In the steep mountain slopes of Ifugao', 'Along the coastal hills of Palawan', 'In the volcanic valleys of Albay'],
                'correct' => 1
            ],
            [
                'type' => 'multichoice',
                'question' => 'What tools were used by ancient builders to construct the terraces?',
                'options' => ['Modern bulldozers and excavators', 'Primitive stone and wooden tools', 'Steam-powered machinery', 'Heavy iron drilling equipment'],
                'correct' => 1
            ],
            [
                'type' => 'multichoice',
                'question' => 'Where does the water for the terrace irrigation system originate?',
                'options' => ['Underground thermal springs', 'High-altitude rainforests', 'Man-made concrete reservoirs', 'Coastal desalination facilities'],
                'correct' => 1
            ],
            [
                'type' => 'multichoice',
                'question' => 'How does the gravity-fed canal network prevent landslides?',
                'options' => ['By pumping water back up into the clouds', 'By channeling mountain runoff evenly from high to low tiers', 'By drying out the soil completely', 'By diverting all rainfall into deep caves'],
                'correct' => 1
            ],
            [
                'type' => 'multichoice',
                'question' => 'What is the traditional Ifugao cooperative labor system called?',
                'options' => ['Bayanihan', 'Ubbu', 'Pagtutulungan', 'Kasama'],
                'correct' => 1
            ],
            [
                'type' => 'multichoice',
                'question' => 'How do farming clans rebuild damaged terrace walls after severe typhoons?',
                'options' => ['They hire commercial construction companies', 'They pool collective labor to gather river stones without exchanging money', 'They abandon damaged fields permanently', 'They replace stone walls with concrete barriers'],
                'correct' => 1
            ],
            [
                'type' => 'multichoice',
                'question' => 'How are water rotation schedules and pest management regulated?',
                'options' => ['By government agricultural inspectors', 'By village elders using seasonal astronomical observations', 'By computer automated weather systems', 'By commercial pesticide dealers'],
                'correct' => 1
            ],
            [
                'type' => 'multichoice',
                'question' => 'What modern challenges threaten the continued survival of the terraces?',
                'options' => ['Overabundance of farm workers and flooding', 'Climate-induced droughts and youth migration to urban centers', 'Excessive foreign tourism and lack of rain', 'Industrial mining inside the terraces'],
                'correct' => 1
            ],
            [
                'type' => 'multichoice',
                'question' => 'What does the term \'hydraulic design\' mean in the context of the passage?',
                'options' => ['A method for extracting precious metals', 'An engineering system designed to manage and channel water flow', 'A technique for carving wooden farm implements', 'A mathematical formula for measuring slope height'],
                'correct' => 1
            ],
            [
                'type' => 'multichoice',
                'question' => 'What is the author\'s main conclusion regarding the preservation of the terraces?',
                'options' => ['The terraces should be converted into commercial tourist resorts', 'Preservation requires honoring indigenous knowledge systems alongside physical repairs', 'Modern machinery should replace all manual farming traditions', 'The terraces have outlived their agricultural usefulness'],
                'correct' => 1
            ]
        ]
    ],

    // ==========================================
    // GRADE 8 - POST-TEST (Instructional)
    // ==========================================
    [
        'title' => 'GRADE 8 - POST-TEST - Coral Reef Conservation in the Coral Triangle',
        'test_type' => 'Post-Test',
        'target_profile' => 'Instructional',
        'target_grade' => 8,
        'status' => 'Published',
        'passage_text' => '<p>The marine waters of the Philippines lie at the very heart of the Coral Triangle, a vast tropical oceanic region often referred to as the \'Amazon of the Seas.\' Despite occupying less than one percent of the global ocean surface, this biodiverse region contains over seventy-five percent of all known coral species and supports thousands of marine creatures, ranging from microscopic plankton to giant manta rays and endangered sea turtles.</p><p>Corals are not inanimate minerals; they are colonial marine invertebrates composed of thousands of tiny organisms called polyps. These polyps live in an intricate symbiotic relationship with microscopic photosynthetic algae known as zooxanthellae. The algae reside inside the translucent tissues of the coral, producing essential nutrients through photosynthesis and giving corals their vibrant coloration. In exchange, coral polyps supply the algae with shelter and vital carbon dioxide.</p><p>This fragile relationship is increasingly endangered by rising sea temperatures and marine pollution. When sea temperatures rise even one or two degrees above average for extended periods, heat-stressed corals expel their symbiotic algae in a distressing process known as coral bleaching. Deprived of their primary food producers, the ghostly white coral skeletons weaken and will perish unless water conditions stabilize quickly.</p><p>To combat this crisis, coastal municipalities across the archipelago are establishing community-enforced Marine Protected Areas (MPAs). In these designated sanctuaries, commercial fishing and anchoring are strictly prohibited, giving degraded reefs a protected window to regenerate. Studies show that well-managed MPAs not only revive coral health but also create a \'spillover effect,\' boosting fish populations in surrounding open waters.</p>',
        'questions' => [
            [
                'type' => 'multichoice',
                'question' => 'Why is the Coral Triangle referred to as the \'Amazon of the Seas\'?',
                'options' => ['It has dense underwater rainforest trees', 'It possesses extraordinary marine biodiversity and most known coral species', 'It is located along the Amazon River in South America', 'It is the coldest oceanic region on the planet'],
                'correct' => 1
            ],
            [
                'type' => 'multichoice',
                'question' => 'What are corals scientifically classified as in the text?',
                'options' => ['Non-living sedimentary rock formations', 'Colonial marine invertebrates composed of tiny polyps', 'A species of large marine flowering plants', 'Deep-sea freshwater minerals'],
                'correct' => 1
            ],
            [
                'type' => 'multichoice',
                'question' => 'What are zooxanthellae, and what is their role in coral biology?',
                'options' => ['Parasitic bacteria that destroy coral structures', 'Photosynthetic algae that produce nutrients and vibrant color', 'Tiny crustaceans that bore holes into limestone', 'Predatory fish that feed on coral polyps'],
                'correct' => 1
            ],
            [
                'type' => 'multichoice',
                'question' => 'What do coral polyps provide to the symbiotic algae in return?',
                'options' => ['Shelter and vital carbon dioxide', 'Direct sunlight and cold water', 'Calcium carbonate food tablets', 'Protection from deep ocean currents'],
                'correct' => 0
            ],
            [
                'type' => 'multichoice',
                'question' => 'What causes the destructive process known as \'coral bleaching\'?',
                'options' => ['Overpopulation of manta rays and sea turtles', 'Heat stress from rising ocean temperatures causing corals to expel algae', 'Lack of salt in oceanic waters', 'Excessive depth of the coral beds'],
                'correct' => 1
            ],
            [
                'type' => 'multichoice',
                'question' => 'What happens to corals if bleaching conditions persist for an extended period?',
                'options' => ['They grow twice as fast as before', 'They transform into hard granite rock', 'They weaken and perish from lack of nutrition', 'They migrate to colder arctic waters'],
                'correct' => 2
            ],
            [
                'type' => 'multichoice',
                'question' => 'What conservation measure are coastal municipalities enforcing across the country?',
                'options' => ['Commercial dredging of the sea floor', 'Establishing community-enforced Marine Protected Areas (MPAs)', 'Constructing artificial islands out of plastic waste', 'Introducing predatory fish into coral lagoons'],
                'correct' => 1
            ],
            [
                'type' => 'multichoice',
                'question' => 'Which human activities are strictly prohibited inside designated marine sanctuaries?',
                'options' => ['Scientific observation and snorkeling', 'Commercial fishing and boat anchoring', 'Swimming and photography', 'Rainwater collection'],
                'correct' => 1
            ],
            [
                'type' => 'multichoice',
                'question' => 'What is the \'spillover effect\' described in the passage?',
                'options' => ['Oil leaks spreading from damaged tanker ships', 'Marine protected areas boosting fish populations in nearby open fishing zones', 'Coral polyps falling onto sandy sea floors', 'Storm surges flooding coastal settlements'],
                'correct' => 1
            ],
            [
                'type' => 'multichoice',
                'question' => 'What can be inferred about the future of coral reefs from this passage?',
                'options' => ['Their survival depends on reducing global warming and maintaining local protected areas', 'Corals are indestructible and do not need human protection', 'Marine sanctuaries do not benefit coastal fishermen', 'Reefs can survive without photosynthetic algae indefinitely'],
                'correct' => 0
            ]
        ]
    ],

    // ==========================================
    // GRADE 9 - PRE-TEST (Instructional)
    // ==========================================
    [
        'title' => 'GRADE 9 - PRE-TEST - Critical Thinking in the Digital Media Age',
        'test_type' => 'Pre-Test',
        'target_profile' => 'Instructional',
        'target_grade' => 9,
        'status' => 'Published',
        'passage_text' => '<p>In today\'s interconnected digital landscape, information circulates faster than at any time in human history. Social media algorithms are deliberately engineered to maximize user engagement by promoting sensational, emotionally charged, and polarizing content. As a consequence, digital consumers frequently find themselves enclosed within \'filter bubbles\' and echo chambers that reinforce existing beliefs while systematically hiding alternative perspectives.</p><p>This automated environment has accelerated the rapid spread of both misinformation, which is false information shared without harmful intent, and disinformation, which is deliberately fabricated falsehood designed to deceive or manipulate the public. Recent advancements in artificial intelligence have also introduced synthetic media, commonly known as \'deepfakes,\' making it increasingly difficult to discern genuine audiovisual recordings from fabricated ones using visual inspection alone.</p><p>To navigate this complex information ecosystem responsibly, educators emphasize the practice of \'lateral reading.\' Unlike traditional vertical reading, where a reader remains on a single website to evaluate its appearance and claims, lateral reading requires users to leave the initial web page immediately. Fact-checkers open multiple browser tabs to investigate the organization behind the publication, verify author credentials, cross-reference assertions against established primary sources, and evaluate funding transparency.</p><p>Ultimately, becoming an informed digital citizen requires intellectual humility and disciplined verification habits. Rather than passively accepting captivating headlines or confirming preconceived biases, critical thinkers pause to verify before sharing. In an age of algorithmic amplification, evaluating information integrity is no longer merely an academic skill; it is a vital civic responsibility.</p>',
        'questions' => [
            [
                'type' => 'multichoice',
                'question' => 'What is the primary engineering objective of social media algorithms as explained in the text?',
                'options' => ['To educate users on scientific topics', 'To maximize engagement by promoting sensational and emotionally charged content', 'To verify the accuracy of news articles before posting', 'To connect users with opposing points of view'],
                'correct' => 1
            ],
            [
                'type' => 'multichoice',
                'question' => 'What is an \'echo chamber\' in the context of digital media consumption?',
                'options' => ['An online audio recording studio', 'An information bubble that reinforces existing views while filtering out opposing views', 'A website with superior sound quality', 'A chat room used exclusively by academic researchers'],
                'correct' => 1
            ],
            [
                'type' => 'multichoice',
                'question' => 'How does the passage distinguish misinformation from disinformation?',
                'options' => ['Misinformation is shared without malicious intent, while disinformation is deliberately deceptive', 'Misinformation is printed on paper, while disinformation is digital', 'Misinformation is always illegal, while disinformation is harmless', 'There is no difference between the two terms'],
                'correct' => 0
            ],
            [
                'type' => 'multichoice',
                'question' => 'What is \'synthetic media\' commonly referred to in the passage?',
                'options' => ['Viral memes', 'Deepfakes', 'Podcasts', 'Infographics'],
                'correct' => 1
            ],
            [
                'type' => 'multichoice',
                'question' => 'Why is simple visual inspection no longer adequate to verify audio and video recordings?',
                'options' => ['Screens have lower resolution than before', 'AI technologies can generate hyper-realistic fabricated media', 'Digital videos are too short to analyze', 'Modern cameras have distorted lenses'],
                'correct' => 1
            ],
            [
                'type' => 'multichoice',
                'question' => 'What is the core method of \'lateral reading\'?',
                'options' => ['Reading from right to left across a printed page', 'Opening multiple browser tabs to verify the source through external corroboration', 'Reading only the comments section of an article', 'Staying on a single website and admiring its graphic design'],
                'correct' => 1
            ],
            [
                'type' => 'multichoice',
                'question' => 'How does lateral reading differ from vertical reading?',
                'options' => ['Vertical reading investigates external sources; lateral reading reads vertically', 'Vertical reading stays on one website; lateral reading checks external credibility', 'Vertical reading is done by fact-checkers; lateral reading is done by algorithms', 'Vertical reading is faster than lateral reading'],
                'correct' => 1
            ],
            [
                'type' => 'multichoice',
                'question' => 'Which factor should fact-checkers examine when evaluating source credibility?',
                'options' => ['Author credentials, primary sources, and funding transparency', 'The number of likes and shares on social media', 'The bright colors of the website banner', 'The speed at which the website loads images'],
                'correct' => 0
            ],
            [
                'type' => 'multichoice',
                'question' => 'What essential habit does the author recommend for critical digital thinkers?',
                'options' => ['Sharing breaking news instantly before others', 'Pausing to verify information integrity before reacting or sharing', 'Relying exclusively on content recommended by social algorithms', 'Avoiding the internet entirely'],
                'correct' => 1
            ],
            [
                'type' => 'multichoice',
                'question' => 'Why does the author consider information evaluation a \'vital civic responsibility\'?',
                'options' => ['It helps social media companies earn advertising profits', 'Unchecked falsehoods can deceive citizens and undermine democratic decision-making', 'It guarantees good grades on standardized examinations', 'It reduces electricity consumption on mobile phones'],
                'correct' => 1
            ]
        ]
    ],

    // ==========================================
    // GRADE 9 - POST-TEST (Instructional)
    // ==========================================
    [
        'title' => 'GRADE 9 - POST-TEST - Telemedicine in Island Communities',
        'test_type' => 'Post-Test',
        'target_profile' => 'Instructional',
        'target_grade' => 9,
        'status' => 'Published',
        'passage_text' => '<p>Providing equitable healthcare across the Philippine archipelago has long been hindered by extreme geographic fragmentation. Remote island municipalities, categorized as Geographically Isolated and Disadvantaged Areas (GIDA), often lack resident medical specialists, diagnostic laboratory equipment, and surgical operating facilities. For sick villagers, obtaining advanced medical attention previously required expensive, multi-hour boat journeys across hazardous seas to mainland provincial hospitals.</p><p>To overcome these persistent barriers, the Department of Health partnered with university researchers to implement telemedicine networks in remote coastal outposts. Rural health units were equipped with high-speed satellite internet receivers, digital stethoscopes, portable ultrasound devices, and cloud-synchronized electronic health record systems. Local community nurses and midwives were trained to perform preliminary clinical examinations using these digital tools.</p><p>Through encrypted video conferencing, provincial physicians and specialist doctors in metropolitan hospitals can now evaluate patients in real time. A dermatologist hundreds of kilometers away can inspect skin lesions via high-resolution video, while a cardiologist in Manila can review real-time electrocardiogram (ECG) data transmitted directly from an island clinic. Prescriptions and individualized therapy plans are issued immediately, allowing patients to receive targeted treatment without leaving their hometowns.</p><p>While telemedicine cannot replace emergency surgical facilities, it has dramatically reduced unnecessary medical transfers, prevented disease complications through early detection, and lessened financial distress for rural families. Expanding rural electrical grids and ensuring reliable satellite bandwidth will be vital to sustaining this digital health revolution across all island frontiers.</p>',
        'questions' => [
            [
                'type' => 'multichoice',
                'question' => 'What does the acronym \'GIDA\' represent in Philippine healthcare planning?',
                'options' => ['General Island Diagnostic Authority', 'Geographically Isolated and Disadvantaged Areas', 'Government Institute for Digital Assistance', 'Global Immunization Development Agency'],
                'correct' => 1
            ],
            [
                'type' => 'multichoice',
                'question' => 'What major hardship did island villagers face when seeking specialist medical care in the past?',
                'options' => ['Navigating complex online scheduling systems', 'Costly and hazardous boat journeys to mainland hospitals', 'Excessive numbers of competing hospitals on the island', 'Having too many doctors visit their homes'],
                'correct' => 1
            ],
            [
                'type' => 'multichoice',
                'question' => 'What technological infrastructure was supplied to the rural health clinics?',
                'options' => ['Commercial advertising screens', 'Satellite internet, digital diagnostic devices, and electronic health records', 'Heavy diesel mining generators', 'Automated pharmacy vending kiosks'],
                'correct' => 1
            ],
            [
                'type' => 'multichoice',
                'question' => 'Who conducts the initial patient examinations at the rural island health units?',
                'options' => ['Visiting mainland tourists', 'Local community nurses and midwives trained in digital tools', 'School teachers and volunteers', 'Military medical officers'],
                'correct' => 1
            ],
            [
                'type' => 'multichoice',
                'question' => 'How do specialist doctors in metropolitan hospitals examine remote patients?',
                'options' => ['Through encrypted real-time video conferences and synchronized digital data', 'By sending handwritten letters through the postal mail', 'By asking patients to travel to Manila each week', 'Through automated telephone answering machines'],
                'correct' => 0
            ],
            [
                'type' => 'multichoice',
                'question' => 'What specific medical specialty example is mentioned regarding cardiac care?',
                'options' => ['A brain surgeon performing surgery via satellite', 'A cardiologist in Manila reviewing real-time electrocardiogram (ECG) data', 'An optometrist prescribing eyeglasses through email', 'A dentist extracting teeth over video call'],
                'correct' => 1
            ],
            [
                'type' => 'multichoice',
                'question' => 'What happens to prescriptions and treatment plans after a telemedicine consultation?',
                'options' => ['They are delayed until the patient visits the mainland', 'They are issued immediately so patients can receive targeted treatment locally', 'They are cancelled by the provincial governor', 'They are stored on paper files for future years'],
                'correct' => 1
            ],
            [
                'type' => 'multichoice',
                'question' => 'What major medical limitation of telemedicine is openly recognized in the text?',
                'options' => ['It cannot conduct simple blood pressure checks', 'It cannot replace emergency surgical operating facilities', 'It is only functional during the daytime', 'Doctors refuse to talk to nurses over video'],
                'correct' => 1
            ],
            [
                'type' => 'multichoice',
                'question' => 'What economic benefit did rural families gain from the telemedicine network?',
                'options' => ['They were exempt from paying any local taxes', 'They reduced travel expenses and avoided costly mainland transfers', 'They received cash allowances for every appointment', 'They sold internet access to tourists'],
                'correct' => 1
            ],
            [
                'type' => 'multichoice',
                'question' => 'What two infrastructure requirements are crucial to sustain the telemedicine program?',
                'options' => ['More paved airport runways and commercial ports', 'Expanding rural electrical grids and ensuring reliable satellite bandwidth', 'Larger hospital parking lots and luxury waiting rooms', 'Manufacturing diesel generators locally'],
                'correct' => 1
            ]
        ]
    ],

    // ==========================================
    // GRADE 10 - PRE-TEST (Instructional)
    // ==========================================
    [
        'title' => 'GRADE 10 - PRE-TEST - Historic Urban Conservation and Adaptive Reuse',
        'test_type' => 'Pre-Test',
        'target_profile' => 'Instructional',
        'target_grade' => 10,
        'status' => 'Published',
        'passage_text' => '<p>Across expanding metropolitan centers in the Philippines, rapid modernization frequently places architectural heritage in direct conflict with real estate development. Centuries-old ancestral mansions, colonial civic plazas, and mid-century modern edifices are frequently demolished to make way for high-rise condominiums and commercial shopping complexes. Critics of preservation often argue that maintaining antiquated structures is economically impractical and impedes urban progress.</p><p>In response to this dilemma, progressive urban planners and architectural historians advocate for \'adaptive reuse.\' This conservation strategy involves repurposing historical buildings for contemporary functions while meticulously preserving their exterior facades, structural heritage, and distinct cultural character. Instead of abandoning historic structures to decay or condemning them to the wrecking ball, adaptive reuse revitalizes buildings as libraries, boutique cultural hotels, art galleries, educational centers, or civic offices.</p><p>Case studies from historic districts like the Heritage City of Vigan and Manila\'s Intramuros demonstrate the tangible economic benefits of adaptive reuse. Restored architectural corridors stimulate heritage tourism, generating sustainable employment for local artisans, restaurateurs, and hospitality providers. Furthermore, studies in urban ecology demonstrate that retrofitting existing brick and hardwood structures generates significantly less carbon emissions and demolition landfill waste than building entirely new concrete towers from scratch.</p><p>Ultimately, a city\'s historical buildings serve as irreplaceable tangible links to its cultural identity and collective memory. Preserving these historic monuments does not require freezing urban centers in the past; rather, thoughtful adaptive reuse demonstrates that historical authenticity and contemporary socioeconomic vitality can flourish together.</p>',
        'questions' => [
            [
                'type' => 'multichoice',
                'question' => 'What primary urban conflict is identified in the opening paragraph?',
                'options' => ['Competition between public bus and train transportation', 'Architectural heritage preservation versus rapid real estate development', 'Disputes over agricultural land boundaries', 'Conflicts between coastal fishermen and shipping lines'],
                'correct' => 1
            ],
            [
                'type' => 'multichoice',
                'question' => 'What common argument is raised by opponents of architectural conservation?',
                'options' => ['Historical buildings are too sturdy to renovate', 'Preserving antiquated structures is economically impractical and halts progress', 'Old buildings consume less energy than modern ones', 'Heritage structures attract too many foreign visitors'],
                'correct' => 1
            ],
            [
                'type' => 'multichoice',
                'question' => 'What is the core definition of \'adaptive reuse\' in urban planning?',
                'options' => ['Demolishing old buildings to create open green parks', 'Repurposing historic buildings for contemporary functions while conserving their character', 'Building exact plastic replicas of ancient monuments', 'Relocating old buildings to rural provinces'],
                'correct' => 1
            ],
            [
                'type' => 'multichoice',
                'question' => 'Which of the following is an example of adaptive reuse mentioned in the text?',
                'options' => ['Transforming a historic building into a library, gallery, or boutique cultural hotel', 'Paving over a colonial plaza to build a multi-level parking garage', 'Replacing wooden windows with aluminum louvers', 'Selling ancestral land to foreign mining companies'],
                'correct' => 0
            ],
            [
                'type' => 'multichoice',
                'question' => 'Which two Philippine historic districts are highlighted as successful models?',
                'options' => ['Bonifacio Global City and Eastwood', 'Vigan and Intramuros', 'Mactan and Boracay', 'Subic Bay and Clark'],
                'correct' => 1
            ],
            [
                'type' => 'multichoice',
                'question' => 'How does adaptive reuse stimulate economic prosperity in historic communities?',
                'options' => ['By increasing real estate demolition fines', 'By boosting heritage tourism and creating jobs for artisans and service providers', 'By converting all residences into government tax offices', 'By banning private vehicular traffic permanently'],
                'correct' => 1
            ],
            [
                'type' => 'multichoice',
                'question' => 'What ecological advantage does retrofitting old structures offer over new construction?',
                'options' => ['It eliminates the need for electric air conditioning', 'It generates significantly less carbon emissions and landfill waste', 'It increases the amount of concrete produced', 'It cools down the entire surrounding city'],
                'correct' => 1
            ],
            [
                'type' => 'multichoice',
                'question' => 'What do historic monuments represent for a society according to the author?',
                'options' => ['Temporary obstacles to commercial real estate expansion', 'Irreplaceable tangible links to cultural identity and collective memory', 'Costly museum relics that have lost civic value', 'Dangerous fire hazards that should be cleared'],
                'correct' => 1
            ],
            [
                'type' => 'multichoice',
                'question' => 'What false choice does the author reject regarding historic preservation?',
                'options' => ['That cities must choose between economic collapse and war', 'That preservation requires freezing cities in the past rather than coexisting with vitality', 'That historic preservation should be funded only by private donors', 'That stone buildings cannot withstand tropical storms'],
                'correct' => 1
            ],
            [
                'type' => 'multichoice',
                'question' => 'What is the overarching thesis of the passage?',
                'options' => ['All old structures should eventually be replaced by steel skyscrapers', 'Adaptive reuse proves that historical authenticity and modern economic vitality can thrive together', 'Urban development should be prohibited inside all Philippine cities', 'Tourism is the sole purpose of architectural conservation'],
                'correct' => 1
            ]
        ]
    ],

    // ==========================================
    // GRADE 10 - POST-TEST (Instructional)
    // ==========================================
    [
        'title' => 'GRADE 10 - POST-TEST - The Circular Economy and Sustainable Consumption',
        'test_type' => 'Post-Test',
        'target_profile' => 'Instructional',
        'target_grade' => 10,
        'status' => 'Published',
        'passage_text' => '<p>Since the onset of the Industrial Revolution, global economic productivity has been driven almost exclusively by a linear consumption framework commonly described as the \'take-make-dispose\' model. Industrial enterprises extract finite virgin raw materials, manufacture consumer goods, and sell them to consumers who discard them after brief periods of utility. This linear paradigm has precipitated catastrophic ecological crises, including rampant plastic pollution, toxic industrial run-off, and the rapid depletion of critical mineral resources.</p><p>In response to these systemic vulnerabilities, progressive economists, industrial engineers, and environmental scientists advocate for a paradigm shift toward a \'circular economy.\' Unlike linear production, a circular economy is restorative and regenerative by design. It aims to decouple economic prosperity from finite resource consumption by designing waste out of the industrial lifecycle entirely. In a closed-loop system, products and materials are engineered for durability, modular repair, disassembly, and continuous remanufacturing.</p><p>Achieving a fully circular economy requires sweeping systemic innovations across international supply chains. Designers are adopting \'cradle-to-cradle\' principles, where materials are selected for non-toxic biodegradability or infinite recyclability. Simultaneously, business models are shifting from individual product ownership toward service-oriented subscriptions, where manufacturers retain legal ownership of goods and assume direct responsibility for upgrading, repairing, and recycling them at the end of their operational lifespans.</p><p>Transitioning away from centuries-old linear industrial practices requires robust legislative frameworks, corporate accountability, and informed consumer behavior. In developing nations like the Philippines, circular practices must also integrate the informal waste-picker sector into dignified, formal recycling cooperatives. Ultimately, embracing a circular economy is not merely an environmental obligation; it represents the only viable pathway toward sustainable long-term economic resilience.</p>',
        'questions' => [
            [
                'type' => 'multichoice',
                'question' => 'How is the traditional linear economic paradigm summarized in the passage?',
                'options' => ['The \'cradle-to-grave\' lifecycle', 'The \'take-make-dispose\' model', 'The \'extract-preserve-renew\' model', 'The \'buy-borrow-return\' model'],
                'correct' => 1
            ],
            [
                'type' => 'multichoice',
                'question' => 'What severe ecological problems have resulted from the linear consumption framework?',
                'options' => ['Rampant plastic pollution, toxic runoff, and depletion of finite minerals', 'Overabundance of forests and excess clean groundwater', 'Decreasing global consumer demand and factory closures', 'Too many products being repaired instead of sold'],
                'correct' => 0
            ],
            [
                'type' => 'multichoice',
                'question' => 'What is the fundamental goal of a circular economy?',
                'options' => ['To halt all global manufacturing and trade', 'To decouple economic prosperity from finite resource consumption by eliminating waste', 'To increase single-use packaging for hygiene', 'To lower corporate taxes for mining companies'],
                'correct' => 1
            ],
            [
                'type' => 'multichoice',
                'question' => 'How are consumer goods engineered within a closed-loop circular system?',
                'options' => ['For planned obsolescence so they break quickly', 'For durability, modular repair, disassembly, and continuous remanufacturing', 'With cheap non-recyclable synthetic adhesives', 'To be discarded directly into municipal incinerators'],
                'correct' => 1
            ],
            [
                'type' => 'multichoice',
                'question' => 'What do \'cradle-to-cradle\' design principles require when selecting raw materials?',
                'options' => ['Choosing toxic chemical coatings for fast assembly', 'Selecting materials for non-toxic biodegradability or infinite recyclability', 'Using virgin petroleum plastics exclusively', 'Minimizing the lifespan of consumer appliances'],
                'correct' => 1
            ],
            [
                'type' => 'multichoice',
                'question' => 'How is consumer business shifting under the product-as-a-service model?',
                'options' => ['Consumers must permanently buy every tool they use', 'Manufacturers retain ownership and provide subscriptions while maintaining repair responsibility', 'Companies refuse to accept returned equipment', 'Products cannot be upgraded after purchase'],
                'correct' => 1
            ],
            [
                'type' => 'multichoice',
                'question' => 'What responsibility do manufacturers hold under service-oriented subscription models?',
                'options' => ['Dumping defective products into ocean landfills', 'Directly upgrading, repairing, and recycling goods at the end of their operational life', 'Charging customers for recycling permits', 'Eliminating product warranties'],
                'correct' => 1
            ],
            [
                'type' => 'multichoice',
                'question' => 'What specific recommendation does the author make for developing nations such as the Philippines?',
                'options' => ['Banning all domestic recycling activities', 'Integrating the informal waste-picker sector into dignified, formal recycling cooperatives', 'Exporting all domestic municipal plastic to neighboring nations', 'Closing down all municipal waste collection centers'],
                'correct' => 1
            ],
            [
                'type' => 'multichoice',
                'question' => 'What three elements are cited as necessary to transition away from linear practices?',
                'options' => ['Higher advertising budgets, larger malls, and cheaper plastics', 'Robust legislative frameworks, corporate accountability, and informed consumer behavior', 'Lower import tariffs, relaxed pollution laws, and fossil fuels', 'Government subsidies for single-use packaging companies'],
                'correct' => 1
            ],
            [
                'type' => 'multichoice',
                'question' => 'What is the overarching conclusion of the passage regarding circular economics?',
                'options' => ['It is an expensive luxury that developing countries cannot afford', 'It is not merely an environmental duty, but the only viable path to long-term economic resilience', 'It will be quickly replaced by more aggressive resource extraction models', 'It works only for agricultural produce and not manufactured electronics'],
                'correct' => 1
            ]
        ]
    ]
];

echo "Starting seeding of 8 Instructional Course Assessments...\n";

$inserted = 0;
$updated = 0;
$skipped = 0;

foreach ($assessments as $item) {
    $title = $item['title'];
    $grade = $item['target_grade'];
    $profile = $item['target_profile'];
    $type = $item['test_type'];
    $passage = $item['passage_text'];
    $questions_json = json_encode($item['questions'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    $status = $item['status'];

    // Check if test with same title and grade exists
    $stmtCheck = $pdo->prepare("SELECT id FROM course_assessments WHERE title = ? AND target_grade = ?");
    $stmtCheck->execute([$title, $grade]);
    $existing = $stmtCheck->fetch();

    if ($existing) {
        // Update existing record
        $stmtUpdate = $pdo->prepare("UPDATE course_assessments SET test_type = ?, target_profile = ?, status = ?, passage_text = ?, questions_json = ? WHERE id = ?");
        $stmtUpdate->execute([$type, $profile, $status, $passage, $questions_json, $existing['id']]);
        echo " [UPDATED] ID {$existing['id']}: {$title} (Grade {$grade}, {$type})\n";
        $updated++;
    } else {
        // Insert new record
        $stmtInsert = $pdo->prepare("INSERT INTO course_assessments (title, test_type, target_profile, target_grade, status, passage_text, questions_json) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmtInsert->execute([$title, $type, $profile, $grade, $status, $passage, $questions_json]);
        $newId = $pdo->lastInsertId();
        echo " [INSERTED] ID {$newId}: {$title} (Grade {$grade}, {$type})\n";
        $inserted++;
    }
}

echo "\nSummary:\n";
echo " - Inserted: {$inserted}\n";
echo " - Updated:  {$updated}\n";
echo " - Total:    " . count($assessments) . "\n";
echo "Seeding completed successfully!\n";

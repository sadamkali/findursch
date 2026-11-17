<div>
    <section class="bg-gray-50 py-10">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <nav class="mb-5 text-sm text-gray-500"><a href="{{ route('home') }}" class="hover:text-primary">Home</a><span class="mx-2">/</span><a href="{{ route('schools.index') }}" class="hover:text-primary">Schools</a><span class="mx-2">/</span><span class="text-gray-800">{{ $school->name }}</span></nav>
            <div class="grid gap-8 lg:grid-cols-[1fr_320px]">
                <div>
                    <div class="flex flex-wrap items-center gap-2"><span class="text-xs font-semibold uppercase tracking-wider text-primary">{{ $school->type?->name }}</span>@if($school->is_demo)<span class="rounded-full border border-sky-200 bg-sky-50 px-2.5 py-1 text-xs font-semibold text-sky-800">Demo sample · Not verified</span>@else<span class="rounded-full border border-green-200 bg-green-50 px-2.5 py-1 text-xs font-semibold text-green-700">✓ Verified listing</span>@endif @if($school->is_featured)<span class="rounded-full border border-amber-200 bg-amber-50 px-2.5 py-1 text-xs font-semibold text-amber-800">Featured</span>@endif</div>
                    <h1 class="mt-3 text-3xl font-semibold text-gray-900 sm:text-4xl" style="font-family: 'DM Serif Display', serif;">{{ $school->name }}</h1>
                    @if($school->tagline)<p class="mt-2 text-lg text-gray-600">{{ $school->tagline }}</p>@endif
                    <p class="mt-4 text-sm text-gray-500">{{ $school->district?->name }} @if($school->district?->region) · {{ $school->district->region->name }} Region @endif @if($school->subCounty) · {{ $school->subCounty->name }} @endif</p>
                    <div class="mt-6 flex flex-wrap gap-3">
                        @if($school->rating_average > 0)<span class="rounded-lg bg-white px-4 py-2 text-sm font-semibold text-gray-800 shadow-sm">★ {{ number_format($school->rating_average, 1) }} <span class="font-normal text-gray-500">({{ $school->reviews_count }} reviews)</span></span>@else<span class="rounded-lg bg-white px-4 py-2 text-sm text-gray-600 shadow-sm">No reviews yet</span>@endif
                        <span class="rounded-lg bg-white px-4 py-2 text-sm capitalize text-gray-700 shadow-sm">{{ $school->boarding_type }} school</span><span class="rounded-lg bg-white px-4 py-2 text-sm capitalize text-gray-700 shadow-sm">{{ $school->gender_policy }} students</span>
                    </div>
                </div>
                <aside class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
                    @if($school->is_demo)
                        <h2 class="font-semibold text-gray-900">Sample profile</h2>
                        <p class="mt-3 text-sm leading-6 text-gray-600">This is fictional demo data. Contact details, admissions inquiries, and school actions are disabled for this sample listing.</p>
                        <span class="mt-4 inline-flex rounded-full border border-sky-200 bg-sky-50 px-3 py-1 text-xs font-semibold text-sky-800">For preview only</span>
                    @else
                        <h2 class="font-semibold text-gray-900">Contact this school</h2>
                        @if($school->phone)<p class="mt-3 text-sm text-gray-600">Phone: <a class="font-medium text-primary hover:underline" href="tel:{{ $school->phone }}">{{ $school->phone }}</a></p>@endif
                        @if($school->email)<p class="mt-2 break-all text-sm text-gray-600">Email: <a class="font-medium text-primary hover:underline" href="mailto:{{ $school->email }}">{{ $school->email }}</a></p>@endif
                        @if($school->website)<p class="mt-2 break-all text-sm text-gray-600">Website: <a class="font-medium text-primary hover:underline" href="{{ $school->website }}" target="_blank" rel="noopener">{{ $school->website }}</a></p>@endif
                        @if($school->address)<p class="mt-3 text-sm leading-6 text-gray-600">{{ $school->address }}</p>@endif
                        <button type="button" wire:click="toggleSave" class="mt-5 w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm font-semibold text-gray-700 hover:border-primary hover:text-primary">{{ $this->isSaved() ? 'Remove from saved schools' : 'Save this school' }}</button>
                        <a href="#send-inquiry" class="btn-primary mt-3 flex w-full justify-center">Send an inquiry</a>
                    @endif
                </aside>
            </div>
        </div>
    </section>

    <div class="mx-auto grid max-w-7xl gap-10 px-4 py-10 sm:px-6 lg:px-8 lg:grid-cols-[1fr_320px]">
        <main class="min-w-0 space-y-10">
            <section><h2 class="text-2xl font-semibold text-gray-900" style="font-family: 'DM Serif Display', serif;">About the school</h2><div class="mt-4 whitespace-pre-line text-sm leading-7 text-gray-700">{{ $school->description ?: 'The school has not added a description yet.' }}</div><dl class="mt-6 grid gap-4 sm:grid-cols-2">@if($school->founded_year)<div class="rounded-lg bg-gray-50 p-4"><dt class="text-xs text-gray-500">Established</dt><dd class="mt-1 font-semibold text-gray-900">{{ $school->founded_year }}</dd></div>@endif @if($school->student_count_approx)<div class="rounded-lg bg-gray-50 p-4"><dt class="text-xs text-gray-500">Approximate student population</dt><dd class="mt-1 font-semibold text-gray-900">{{ number_format($school->student_count_approx) }}</dd></div>@endif</dl></section>

            @if($school->curricula->isNotEmpty())<section><h2 class="text-2xl font-semibold text-gray-900" style="font-family: 'DM Serif Display', serif;">Curriculum</h2><div class="mt-4 flex flex-wrap gap-2">@foreach($school->curricula as $curriculum)<span class="rounded-full bg-primary-50 px-3 py-1.5 text-sm font-medium text-primary-800">{{ $curriculum->name }} ({{ $curriculum->abbreviation }})</span>@endforeach</div></section>@endif

            @if($school->programs->isNotEmpty())<section><h2 class="text-2xl font-semibold text-gray-900" style="font-family: 'DM Serif Display', serif;">Programmes</h2><div class="mt-4 grid gap-4 sm:grid-cols-2">@foreach($school->programs->where('is_active', true) as $program)<article class="rounded-xl border border-gray-200 p-5"><h3 class="font-semibold text-gray-900">{{ $program->name }}</h3><p class="mt-1 text-xs text-gray-500">{{ $program->level }} @if($program->duration) · {{ $program->duration }} @endif</p>@if($program->curriculum)<p class="mt-2 text-sm text-gray-600">{{ $program->curriculum->name }}</p>@endif @if($program->description)<p class="mt-2 text-sm leading-6 text-gray-600">{{ $program->description }}</p>@endif</article>@endforeach</div></section>@endif

            @if($school->fees->where('is_active', true)->isNotEmpty())<section><h2 class="text-2xl font-semibold text-gray-900" style="font-family: 'DM Serif Display', serif;">School fees</h2><p class="mt-1 text-sm text-gray-500">Fees are provided by the school and should be confirmed directly before making a decision.</p><div class="mt-4 overflow-x-auto rounded-xl border border-gray-200"><table class="w-full text-left text-sm"><thead class="bg-gray-50 text-xs uppercase tracking-wide text-gray-500"><tr><th class="px-4 py-3">Description</th><th class="px-4 py-3">Period</th><th class="px-4 py-3 text-right">Amount</th></tr></thead><tbody class="divide-y divide-gray-100">@foreach($school->fees->where('is_active', true) as $fee)<tr><td class="px-4 py-3"><span class="font-medium text-gray-900">{{ $fee->label }}</span><span class="block text-xs capitalize text-gray-500">{{ $fee->category }}</span></td><td class="px-4 py-3 capitalize text-gray-600">{{ str_replace('_', ' ', $fee->period) }}</td><td class="px-4 py-3 text-right font-semibold text-gray-900">{{ $fee->currency }} {{ number_format((float) $fee->amount) }}</td></tr>@endforeach</tbody></table></div></section>@endif

            @if($school->facilities->isNotEmpty())<section><h2 class="text-2xl font-semibold text-gray-900" style="font-family: 'DM Serif Display', serif;">Facilities</h2><div class="mt-4 flex flex-wrap gap-2">@foreach($school->facilities as $facility)<span class="rounded-lg border border-gray-200 px-3 py-2 text-sm text-gray-700">{{ $facility->name }}</span>@endforeach</div></section>@endif

            <section id="reviews"><h2 class="text-2xl font-semibold text-gray-900" style="font-family: 'DM Serif Display', serif;">Parent and student reviews</h2><p class="mt-1 text-sm text-gray-500">{{ $school->reviews_count }} approved reviews</p><div class="mt-5 space-y-4">@forelse($school->approvedReviews as $review)<article class="rounded-xl border border-gray-200 p-5"><div class="flex flex-wrap items-center justify-between gap-2"><h3 class="font-semibold text-gray-900">{{ $review->title }}</h3><span class="text-sm font-semibold text-amber-700">★ {{ $review->rating }}/5</span></div><p class="mt-3 whitespace-pre-line text-sm leading-6 text-gray-700">{{ $review->body }}</p><p class="mt-3 text-xs text-gray-500">{{ $review->user?->name ?? 'FindurSch user' }} · {{ ucfirst($review->reviewer_type) }}</p>@if($review->owner_response)<div class="mt-4 rounded-lg bg-blue-50 p-4"><p class="text-xs font-semibold uppercase tracking-wide text-blue-800">School response</p><p class="mt-2 whitespace-pre-line text-sm leading-6 text-blue-950">{{ $review->owner_response }}</p></div>@endif</article>@empty<div class="rounded-xl bg-gray-50 p-6 text-sm text-gray-500">No approved reviews have been published yet.</div>@endforelse</div></section>

            @if($school->is_demo)
                <div class="rounded-xl border border-sky-200 bg-sky-50 p-5"><h2 class="font-semibold text-sky-900">Reviews are disabled for demo listings</h2><p class="mt-2 text-sm leading-6 text-sky-800">This fictional sample profile is for preview purposes only. Reviews are available on eligible real school listings.</p></div>
            @else
                <section class="rounded-xl border border-gray-200 p-6"><h2 class="text-xl font-semibold text-gray-900">Write a review</h2>@if(session('review_submitted'))<p class="mt-3 rounded-lg bg-green-50 p-3 text-sm text-green-800">{{ session('review_submitted') }}</p>@endif
                @if($alreadyReviewed)<p class="mt-3 text-sm text-gray-600">You have already submitted a review for this school.</p>
                @elseif(auth()->check() && auth()->user()->hasVerifiedEmail())
                    <form wire:submit="submitReview" class="mt-5 space-y-4">
                        <div class="grid gap-4 sm:grid-cols-2"><div><label class="mb-1 block text-sm font-medium text-gray-700" for="reviewerType">I am a</label><select id="reviewerType" wire:model="reviewerType" class="w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm"><option value="parent">Parent / Guardian</option><option value="student">Student</option><option value="alumni">Alumni</option><option value="staff">Staff</option><option value="other">Other</option></select></div><div><label class="mb-1 block text-sm font-medium text-gray-700" for="reviewRating">Rating</label><select id="reviewRating" wire:model="reviewRating" class="w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm">@for($rating = 5; $rating >= 1; $rating--)<option value="{{ $rating }}">{{ $rating }} out of 5</option>@endfor</select></div></div>
                        <div><label for="reviewTitle" class="mb-1 block text-sm font-medium text-gray-700">Review title</label><input id="reviewTitle" wire:model="reviewTitle" required maxlength="255" class="w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm">@error('reviewTitle')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror</div>
                        <div><label for="reviewBody" class="mb-1 block text-sm font-medium text-gray-700">Your experience</label><textarea id="reviewBody" wire:model="reviewBody" required minlength="20" maxlength="5000" rows="5" class="w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm"></textarea>@error('reviewBody')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror</div>
                        <button type="submit" wire:loading.attr="disabled" class="btn-primary">Submit review</button>
                    </form>
                @elseif(auth()->check())<p class="mt-3 text-sm text-gray-600">Verify your email address before submitting a review. <a class="font-semibold text-primary hover:underline" href="{{ route('verification.notice') }}">Verify email</a></p>
                @else<p class="mt-3 text-sm text-gray-600"><a class="font-semibold text-primary hover:underline" href="{{ route('login') }}">Sign in</a> to leave a review.</p>@endif
            </section>
            @endif

            @if($school->is_demo)
                <section id="send-inquiry" class="rounded-xl border border-sky-200 bg-sky-50 p-6"><h2 class="text-xl font-semibold text-sky-900">Inquiries are disabled for this demo</h2><p class="mt-2 text-sm leading-6 text-sky-800">This sample school is fictional. Inquiry and admissions actions become available for eligible verified school listings.</p></section>
            @else
                <section id="send-inquiry" class="rounded-xl border border-gray-200 p-6"><h2 class="text-xl font-semibold text-gray-900">Send an inquiry</h2>@if(session('inquiry_sent'))<p class="mt-3 rounded-lg bg-green-50 p-3 text-sm text-green-800">{{ session('inquiry_sent') }}</p>@endif
                @auth
                    @if(auth()->user()->hasVerifiedEmail())
                        <form wire:submit="submitInquiry" class="mt-5 space-y-4">
                            <div class="grid gap-4 sm:grid-cols-2"><div><label for="inquiryPhone" class="mb-1 block text-sm font-medium text-gray-700">Phone number</label><input id="inquiryPhone" wire:model="inquiryPhone" maxlength="30" class="w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm">@error('inquiryPhone')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror</div><div><label for="inquiryStudentName" class="mb-1 block text-sm font-medium text-gray-700">Student name (optional)</label><input id="inquiryStudentName" wire:model="inquiryStudentName" maxlength="255" class="w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm"></div><div><label for="inquiryStudentAge" class="mb-1 block text-sm font-medium text-gray-700">Student age</label><input id="inquiryStudentAge" type="number" min="1" max="100" wire:model="inquiryStudentAge" class="w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm">@error('inquiryStudentAge')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror</div><div><label for="inquiryLevel" class="mb-1 block text-sm font-medium text-gray-700">Intended class / level</label><input id="inquiryLevel" wire:model="inquiryLevel" maxlength="120" class="w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm"></div></div>
                            <div><label for="inquiryMessage" class="mb-1 block text-sm font-medium text-gray-700">Message</label><textarea id="inquiryMessage" wire:model="inquiryMessage" required minlength="10" maxlength="5000" rows="5" class="w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm"></textarea>@error('inquiryMessage')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror</div>
                            <button type="submit" wire:loading.attr="disabled" class="btn-primary">Send inquiry</button>
                        </form>
                    @else<p class="mt-3 text-sm text-gray-600">Verify your email before sending an inquiry. <a class="font-semibold text-primary hover:underline" href="{{ route('verification.notice') }}">Verify email</a></p>@endif
                @else<p class="mt-3 text-sm text-gray-600"><a class="font-semibold text-primary hover:underline" href="{{ route('login') }}">Sign in</a> to contact this school.</p>@endauth
            </section>
            @endif
        </main>

        <aside class="space-y-5">
            @if($school->contacts->isNotEmpty())<section class="rounded-xl border border-gray-200 p-5"><h2 class="font-semibold text-gray-900">More contact options</h2><div class="mt-4 space-y-3">@foreach($school->contacts as $contact)<div><p class="text-xs text-gray-500">{{ $contact->label ?: ucfirst($contact->type) }}</p>@if($contact->type === 'email')<a class="break-all text-sm font-medium text-primary hover:underline" href="mailto:{{ $contact->value }}">{{ $contact->value }}</a>@elseif(in_array($contact->type, ['phone', 'whatsapp']))<a class="break-all text-sm font-medium text-primary hover:underline" href="tel:{{ $contact->value }}">{{ $contact->value }}</a>@elseif($contact->type === 'website')<a class="break-all text-sm font-medium text-primary hover:underline" href="{{ $contact->value }}" target="_blank" rel="noopener">{{ $contact->value }}</a>@else<span class="break-all text-sm text-gray-700">{{ $contact->value }}</span>@endif</div>@endforeach</div></section>@endif
            <section class="rounded-xl bg-gray-50 p-5"><h2 class="font-semibold text-gray-900">About this listing</h2>@if($school->is_demo)<p class="mt-2 text-sm leading-6 text-gray-600">This is a fictional demonstration profile created to preview FindurSch. It is not a real school, is not verified, and has no real contact or admissions information.</p>@else<p class="mt-2 text-sm leading-6 text-gray-600">This profile is marked as verified by FindurSch. School details and fees can change, so confirm current information directly with the school.</p>@endif</section>
        </aside>
    </div>
</div>

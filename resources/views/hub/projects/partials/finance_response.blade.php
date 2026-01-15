{{-- resources/views/hub/projects/partials/finance_response.blade.php --}}

@include('hub.projects.partials.finance', [
  'project'       => $project,
  'financeErrors' => $financeErrors ?? null,
  'sectionWrap'   => $sectionWrap ?? "overflow-hidden rounded-2xl",
  'sectionHeader' => $sectionHeader ?? "shrink-0 px-6 py-4 bg-[#191D38]/10",
  'sectionBody'   => $sectionBody ?? "bg-[#191D38]/5",
])

@include('hub.projects.partials.logbook', [
  'project'       => $project,
  'sectionWrap'   => $sectionWrap ?? "overflow-hidden rounded-2xl",
  'sectionHeader' => $sectionHeader ?? "shrink-0 px-6 py-4 bg-[#191D38]/10",
  'sectionBody'   => $sectionBody ?? "bg-[#191D38]/5",
  'oob'           => true,
])

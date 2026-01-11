# TODO List for User Requests

## Task 1: Modify Photo Selection in Lapor Rusak
- [ ] Update resources/views/mobil/lapor-rusak.blade.php to use getUserMedia API for direct camera access instead of file input
- [ ] Replace the file input with a video element and buttons for capture and retake
- [ ] Update JavaScript to handle camera access, photo capture, and preview

## Task 2: Add Detail Kerusakan Button in Admin Car List
- [ ] Modify resources/views/mobil/index.blade.php to add "Detail Kerusakan" button for damaged cars
- [ ] Add new method `detailKerusakan` to MobilController.php
- [ ] Add new route for detail kerusakan in routes/web.php
- [ ] Create new view resources/views/mobil/detail-kerusakan.blade.php to display damage details, photos, and other data

## Testing and Finalization
- [ ] Test both features to ensure they work correctly
- [ ] Update TODO.md with completion status

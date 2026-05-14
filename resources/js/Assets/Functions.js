export function hasErrors(form, field) {
	return form.errors && form.errors[field] ? true : false;
}

VZ Upload for FieldFrame
========================

THIS FIELDTYPE IS NO LONGER BEING MAINTANINED. [NGen's File Field](http://www.ngenworks.com/software/ee/ngen-file-field/) is more stable and I am now using it for my own projects.

Currently there is no check when uploading that the file does not already exist. If you upload the same file twice from different weblog entries and then delete one, they will both be deleted!


Usage
-----

Single tag:
{field_name} will output the url of the first file.

Tag pair:
{field_name}....{/field_name} will loop over the contents of the tag pair, substituting the following tags as appropriate.

- {file_url} The url of the uploaded file (http://test.com/images/photo.jpg)
- {file_name} Just the name of the file (photo.jpg)
- {count} The number of the current file
- {total_results} The total number of files in this field


Example
-------

	<ul>
	{files}
		<li><img src="{file_url}" alt="File {count} of {total_results}" /></li>
	{/files}
	</ul>


To-Do
-----

- Check if the file is already on the server and offer to rename
- Make it work with FF Matrix
- Figure out how to handle files that are uploaded, but the entry is not saved

<?php

namespace App\Service;

use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\File\Exception\FileException;

class FileUploader
{
	private $slugger;

	private $targetDirectory_actu;

	private $targetDirectory_photo;

	public function __construct($targetDirectory_actu, $targetDirectory_photo, SluggerInterface $slugger)
	{
		$this->targetDirectory_actu = $targetDirectory_actu;
		$this->targetDirectory_photo = $targetDirectory_photo;
		$this->slugger = $slugger;
	}

	public function upload(UploadedFile $file, $directory = 'photo')
	{
		$originalFilename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
		$safeFilename = $this->slugger->slug($originalFilename);
		$fileName = $safeFilename.'-'.uniqid().'.'.$file->guessExtension();

		try {
			$directory == 'photo'
				? $file->move($this->targetDirectory_photo(), $fileName)
				: $file->move($this->targetDirectory_actu(), $fileName)
			;
		} catch (FileException $e) {
			// ... handle exception if something happens during file upload
			return null; // for example
		}
		return $fileName;
	}

	public function targetDirectory_actu()
	{
		return $this->targetDirectory_actu;
	}

	public function targetDirectory_photo()
	{
		return $this->targetDirectory_photo;
	}

	/**
	 * Supprime les photos du dossier qui ne sont plus associé à une actualité en db
	 */
	public function cleanPhotosActu($actuRepository){

		// Get all photos name
		$photosInDb = [];
		$photosInDbByActu = $actuRepository->getPhotosName();

		foreach($photosInDbByActu as $photoInDbByActu){
			foreach($photoInDbByActu as $photoInDb){
				$photoInDb != null
					? $photosInDb[] = $photoInDb
					: null
				;
			}
		}

		// Delete unlink photos
		$photosInFolder = scandir($this->targetDirectory_actu(), 1);
		foreach ($photosInFolder as $key => $photoInFolder){
			if ($photoInFolder != '.' && $photoInFolder != '..' && !in_array($photoInFolder, $photosInDb)){
				unlink($this->targetDirectory_actu().'/'.$photoInFolder);
			}
		}

		return true;
	}

	/**
	 * Supprime les photos du dossier qui ne sont plus associé à une photo en db
	 */
	public function cleanPhotos($photoRepository){

		// Get all photos name
		$photosInDb = [];
		$photosInDbByPhoto = $photoRepository->getPhotosName();

		foreach($photosInDbByPhoto as $photoInDbByPhoto){
			foreach($photoInDbByPhoto as $photoInDb){
				$photoInDb != null
					? $photosInDb[] = $photoInDb
					: null
				;
			}
		}

		// Delete unlink photos
		$photosInFolder = scandir($this->targetDirectory_photo(), 1);
		foreach ($photosInFolder as $key => $photoInFolder){
			if ($photoInFolder != '.' && $photoInFolder != '..' && !in_array($photoInFolder, $photosInDb)){
				unlink($this->targetDirectory_photo().'/'.$photoInFolder);
			}
		}

		return true;
	}
}

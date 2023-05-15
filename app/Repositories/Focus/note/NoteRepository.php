<?php

namespace App\Repositories\Focus\note;

use App\Models\note\Note;
use App\Exceptions\GeneralException;
use App\Repositories\BaseRepository;

/**
 * Class NoteRepository.
 */
class NoteRepository extends BaseRepository
{
    /**
     * Associated Repository Model.
     */
    const MODEL = Note::class;

    /**
     * This method is used by Table Controller
     * For getting the table data to show in
     * the grid
     * @return mixed
     */
    public function getForDataTable()
    {
        $q = $this->query();

        if (request('project_id')) {
            $q->whereHas('project', fn($q) => $q->where('projects.id', request('project_id')));
        }

        return $q->get();
    }

    /**
     * For Creating the respective model in storage
     *
     * @param array $input
     * @return App\Models\note\Note $note;
     * @throws GeneralException
     */
    public function create(array $input)
    {
        $input['title'] = strip_tags($input['title']);
        $input['content'] = clean(html_entity_decode($input['content']), 'purifier.settings.custom_definition');
        $note = Note::create($input);
        return $note;
    }

    /**
     * For updating the respective Model in storage
     *
     * @param Note $note
     * @param  $input
     * @throws GeneralException
     * return bool
     */
    public function update(Note $note, array $input)
    {
        $input['title'] = strip_tags($input['title']);
        $input['content'] = clean(html_entity_decode($input['content']), 'purifier.settings.custom_definition');
        if ($note->update($input)) return $note;
    }

    /**
     * For deleting the respective model from storage
     *
     * @param Note $note
     * @return bool
     * @throws GeneralException
     */
    public function delete(Note $note)
    {
        if ($note->delete()) return true;
    }
}

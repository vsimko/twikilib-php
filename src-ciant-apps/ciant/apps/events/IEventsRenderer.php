<?php
namespace ciant\apps\events;

use ciant\factory\ExtractedEventsFactory;

/**
 * Classes implementing this interface can be used in the Render
 * @author Viliam Simko
 */
interface IEventsRenderer {

	/**
	 * The $eventsFactory will be used in derived classes to obtain list of events.
	 * @param ciant\factory\ExtractedEventsFactory $eventsFactory
	 */
	function __construct(ExtractedEventsFactory $eventsFactory);

	/**
	 * This is the rendering function you should implement.
	 * The implementation should write to the output buffer (use the "echo" command).
	 *
	 * @return void
	 */
	function render();
}